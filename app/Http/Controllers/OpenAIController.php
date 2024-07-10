<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class OpenAIController extends Controller
{
    private $client;

    public function __construct()
    {
        $apiKey = env('OPEN_AI_SECRET_KEY') ?? throw new \Exception('OpenAI API key is not set');
        $this->client = OpenAI::client($apiKey);
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string',
                'conversation_id' => 'nullable|integer',
            ]);

            $conversationId = $request->input('conversation_id');
            $message = $request->input('message');
            $userId = auth()->id();

            if (!$conversationId) {
                $conversation = $this->createNewConversation($userId);
            } else {
                $conversation = Conversation::findOrFail($conversationId);
                $conversation->checkAndUpdateExpired();
                if ($conversation->status !== Conversation::STATUS_IN_PROGRESS) {
                    return response()->json(['error' => 'This conversation has ended.'], 400);
                }
            }

            $this->saveMessage($conversation->id, $message, 'user');

            $response = $this->getOpenAIResponse($conversation, $message);

            if (strtolower($message) === '対話を完了') {
                $this->completeConversation($conversation);
                return response()->json([
                    'message' => '会話を終了しました。サマリーを生成中です。',
                    'conversation_id' => $conversation->id,
                    'status' => 'completed'
                ]);
            }

            $conversation->last_activity_at = Carbon::now();
            $conversation->save();

            return response()->json([
                'message' => $response,
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in sendMessage: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    private function createNewConversation($userId)
    {
        return Conversation::create([
            'user_id' => $userId,
            'status' => Conversation::STATUS_IN_PROGRESS,
            'last_activity_at' => Carbon::now(),
            'agent_status' => Conversation::AGENT_STATUS_THINKING,
        ]);
    }

    private function getOpenAIResponse(Conversation $conversation, $message)
    {
        $threadId = $conversation->thread_id;

        if (!$threadId) {
            $thread = $this->client->threads()->create();
            $conversation->thread_id = $thread->id;
            $conversation->save();
            $threadId = $thread->id;
        }

        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $message,
        ]);

        $conversation->setAgentStatusThinking();

        $run = $this->client->threads()->runs()->create($threadId, [
            'assistant_id' => Config::get('services.openai.assistant_id'),
        ]);

        $isCompleted = $this->waitUntilRunCompleted($threadId, $run->id);
        if (!$isCompleted) {
            throw new \Exception('Failed to complete OpenAI job');
        }

        $response = $this->getLatestAssistantMessage($threadId);

        $this->saveMessage($conversation->id, $response, 'assistant');
        $conversation->setAgentStatusReacted();

        return $response;
    }

    private function completeConversation(Conversation $conversation)
    {
        // 会話を完了状態に設定
        $conversation->markAsCompleted();
    
        // エージェントのステータスを更新
        $conversation->setAgentStatusReacted();
    
        // サマリー生成ジョブをディスパッチ
        GenerateConversationSummaryJob::dispatch($conversation);
    }

       public function generateAndSaveSummary(Conversation $conversation)
    {
        Log::info('Starting summary generation for conversation: ' . $conversation->id);
        
        $threadId = $conversation->thread_id;

        if (!$threadId) {
            Log::error('No thread ID found for conversation: ' . $conversation->id);
            throw new \Exception('No thread ID found for conversation');
        }

        try {
            // サマリー生成メッセージを送信
            $message = $this->client->threads()->messages()->create($threadId, [
                'role' => 'user',
                'content' => '今までの会話の内容を15文字以内で要約し、タイトルをつけてください',
            ]);
            Log::info('Summary request sent to thread ID: ' . $threadId . ', Message ID: ' . $message->id);

            // サマリー生成リクエストを送信
            $run = $this->client->threads()->runs()->create($threadId, [
                'assistant_id' => Config::get('services.openai.assistant_id'),
            ]);
            Log::info('Run request sent for thread ID: ' . $threadId . ', Run ID: ' . $run->id);

            // サマリー生成の完了を待機
            $isCompleted = $this->waitUntilRunCompleted($threadId, $run->id);
            if (!$isCompleted) {
                throw new \Exception('Failed to generate summary: Run not completed');
            }
            Log::info('Summary generation completed for thread ID: ' . $threadId . ', Run ID: ' . $run->id);

            // 最新のサマリーメッセージを取得
            $summaryMessage = $this->getLatestAssistantMessage($threadId);
            Log::info('Retrieved summary message for conversation ID: ' . $conversation->id . ', Message: ' . ($summaryMessage ?? 'No message retrieved'));

            if ($summaryMessage) {
                // サマリーメッセージを保存
                $message = ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'message' => $summaryMessage,
                    'role_id' => 2, // assistant role
                    'summary' => true, // サマリーであることを示す
                    'is_hidden' => false,
                ]);
                Log::info('Summary message saved for conversation ID: ' . $conversation->id . ', Message ID: ' . $message->id);

                // updated_atを更新することでサマリー生成時刻を記録
                $conversation->touch();
                Log::info('Conversation updated_at set to now for conversation ID: ' . $conversation->id);

                return true;
            } else {
                Log::error('No summary message retrieved for conversation ID: ' . $conversation->id);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error in generateAndSaveSummary: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

        private function getLatestAssistantMessage($threadId)
    {
        try {
            $messages = $this->client->threads()->messages()->list($threadId, [
                'order' => 'desc',
                'limit' => 1,
            ]);

            Log::info('Retrieved messages: ' . json_encode($messages));

            if (is_object($messages) && property_exists($messages, 'data') && is_array($messages->data)) {
                foreach ($messages->data as $message) {
                    if (is_object($message) && property_exists($message, 'role') && $message->role === 'assistant') {
                        if (property_exists($message, 'content') && is_array($message->content)) {
                            foreach ($message->content as $content) {
                                if (is_object($content) && property_exists($content, 'type') && $content->type === 'text') {
                                    return $content->text->value;
                                }
                            }
                        }
                    }
                }
            }

            Log::error('No valid assistant message found in the response');
            return null;
        } catch (\Exception $e) {
            Log::error('Error in getLatestAssistantMessage: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    private function saveMessage($conversationId, $message, $role)
    {
        ConversationMessage::create([
            'conversation_id' => $conversationId,
            'message' => $message,
            'role_id' => $role === 'user' ? 1 : 2,
            'is_hidden' => false,
        ]);
    }

        private function waitUntilRunCompleted($threadId, $runId)
    {
        $maxAttempts = Config::get('services.openai.max_attempts', 10);
        $attemptDelay = Config::get('services.openai.attempt_delay', 3);

        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep($attemptDelay);
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
            Log::info('Run status check: ' . $run->status . ' for Run ID: ' . $runId);
            if ($run->status === 'completed') {
                return true;
            } elseif ($run->status === 'failed') {
                Log::error('Run failed for thread ID: ' . $threadId . ', Run ID: ' . $runId);
                return false;
            }
        }

        Log::error('Timeout waiting for run completion. Thread ID: ' . $threadId . ', Run ID: ' . $runId);
        return false;
    }
}