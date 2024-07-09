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
        $this->client = OpenAI::client(Config::get('services.openai.secret_key'));
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

    private function generateAndSaveSummary(Conversation $conversation)
    {
        $threadId = $conversation->thread_id;
    
        // サマリー生成メッセージを送信
        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => 'Please provide a summary of our conversation.',
        ]);
        Log::info('Summary request sent to thread ID: ' . $threadId);
    
        // サマリー生成リクエストを送信
        $run = $this->client->threads()->runs()->create($threadId, [
            'assistant_id' => Config::get('services.openai.assistant_id'),
        ]);
        Log::info('Run request sent for thread ID: ' . $threadId);
    
        // サマリー生成の完了を待機
        $isCompleted = $this->waitUntilRunCompleted($threadId, $run->id);
        if (!$isCompleted) {
            Log::error('Failed to generate summary for conversation: ' . $conversation->id);
            return;
        }
        Log::info('Summary generation completed for thread ID: ' . $threadId);
    
        // 最新のサマリーメッセージを取得
        $summaryMessage = $this->getLatestAssistantMessage($threadId);
        Log::info('Retrieved summary message for conversation ID: ' . $conversation->id);
    
        // サマリーメッセージを保存
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'message' => $summaryMessage,
            'role_id' => 2, // assistant role
            'summary' => true, // サマリーであることを示す
            'is_hidden' => false,
        ]);
        Log::info('Summary message saved for conversation ID: ' . $conversation->id);
    
        // サマリーが生成されたことを Conversation モデルに記録
        $conversation->summary_generated_at = now();
        $conversation->save();
        Log::info('Summary generation time saved for conversation ID: ' . $conversation->id);
    }

    private function getLatestAssistantMessage($threadId)
    {
        $messages = $this->client->threads()->messages()->list($threadId, [
            'order' => 'desc',
            'limit' => 1,
        ]);

        foreach ($messages->data as $message) {
            foreach ($message->content as $content) {
                if ($content->type === 'text') {
                    return $content->text->value;
                }
            }
        }

        return 'No response from OpenAI';
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
            if ($run->status === 'completed') {
                return true;
            }
        }

        return false;
    }
}