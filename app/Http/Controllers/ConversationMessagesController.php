<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use OpenAI;

class ConversationMessagesController extends Controller
{
    private $client;

    public function __construct()
    {
        $apiKey = env('OPEN_AI_SECRET_KEY') ?? throw new \Exception('OpenAI API key is not set');
        $this->client = OpenAI::client($apiKey);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'message' => 'required|string',
            ]);
            
            $user_id = Auth::id();
            $conversation_id = $request->input('conversation_id');
            $message = $request->input('message');

            $conversation = Conversation::findOrFail($conversation_id);
            $conversation->checkAndUpdateExpired();
            
            if ($conversation->status !== Conversation::STATUS_IN_PROGRESS) {
                return response()->json(['error' => 'This conversation has ended.'], 400);
            }

            // Save user message
            $this->saveMessage($conversation_id, $message, 'user');

            // Get OpenAI response
            $response = $this->getOpenAIResponse($conversation, $message);

            // Check if it's a completion request
            if (strtolower($message) === '対話を完了') {
                $conversation->markAsCompleted();
                GenerateConversationSummaryJob::dispatch($conversation);
                return response()->json([
                    'message' => '会話を終了しました。サマリーを生成中です。',
                    'conversation_id' => $conversation->id,
                    'status' => 'completed'
                ]);
            }

            // Save OpenAI response
            $this->saveMessage($conversation_id, $response, 'assistant');

            // Update conversation last activity
            $conversation->last_activity_at = Carbon::now();
            $conversation->save();

            return response()->json([
                'message' => $response,
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in store method: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    private function getOpenAIResponse(Conversation $conversation, $message)
    {
        $threadId = $conversation->thread_id;

        if (!$threadId) {
            $thread = $this->client->threads()->create([]);  // 空の配列を引数として渡す
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

        $conversation->setAgentStatusReacted();

        return $response;
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

    private function getLatestAssistantMessage($threadId)
    {
        try {
            $messages = $this->client->threads()->messages()->list($threadId, [
                'order' => 'desc',
                'limit' => 1,
            ]);

            if (is_object($messages) && property_exists($messages, 'data') && is_array($messages->data)) {
                foreach ($messages->data as $message) {
                    if (is_object($message) && $message->role === 'assistant' && is_array($message->content)) {
                        foreach ($message->content as $content) {
                            if (is_object($content) && $content->type === 'text') {
                                return $content->text->value;
                            }
                        }
                    }
                }
            }

            Log::error('No valid assistant message found in the response');
            return null;
        } catch (\Exception $e) {
            Log::error('Error in getLatestAssistantMessage: ' . $e->getMessage());
            return null;
        }
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

    public function generateAndSaveSummary(Conversation $conversation)
    {
        Log::info('Starting summary generation for conversation: ' . $conversation->id);
        
        $threadId = $conversation->thread_id;

        if (!$threadId) {
            Log::error('No thread ID found for conversation: ' . $conversation->id);
            throw new \Exception('No thread ID found for conversation');
        }

        try {
            // Send summary generation message
            $message = $this->client->threads()->messages()->create($threadId, [
                'role' => 'user',
                'content' => '今までの会話の内容を15文字以内で要約し、タイトルをつけてください',
            ]);
            Log::info('Summary request sent to thread ID: ' . $threadId . ', Message ID: ' . $message->id);

            // Send summary generation request
            $run = $this->client->threads()->runs()->create($threadId, [
                'assistant_id' => Config::get('services.openai.assistant_id'),
            ]);
            Log::info('Run request sent for thread ID: ' . $threadId . ', Run ID: ' . $run->id);

            // Wait for summary generation to complete
            $isCompleted = $this->waitUntilRunCompleted($threadId, $run->id);
            if (!$isCompleted) {
                throw new \Exception('Failed to generate summary: Run not completed');
            }
            Log::info('Summary generation completed for thread ID: ' . $threadId . ', Run ID: ' . $run->id);

            // Get the latest summary message
            $summaryMessage = $this->getLatestAssistantMessage($threadId);
            Log::info('Retrieved summary message for conversation ID: ' . $conversation->id . ', Message: ' . ($summaryMessage ?? 'No message retrieved'));

            if ($summaryMessage) {
                // Save summary message
                $message = ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'message' => $summaryMessage,
                    'role_id' => 2, // assistant role
                    'summary' => true,
                    'is_hidden' => false,
                ]);
                Log::info('Summary message saved for conversation ID: ' . $conversation->id . ', Message ID: ' . $message->id);

                // Update conversation's updated_at to record summary generation time
                $conversation->touch();
                Log::info('Conversation updated_at set to now for conversation ID: ' . $conversation->id);

                return true;
            } else {
                Log::error('No summary message retrieved for conversation ID: ' . $conversation->id);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error in generateAndSaveSummary: ' . $e->getMessage());
            return false;
        }
    }
}
