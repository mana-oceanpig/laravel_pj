<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Support\Facades\Log;

class OpenAIController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPEN_AI_SECRET_KEY'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $assistantId = env('OPEN_AI_ASSISTANT_ID');

        // Create a new conversation thread
        $run = $this->client->threads()->createAndRun([
            'assistant_id' => $assistantId,
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $request->input('message'),
                    ],
                ],
            ],
        ]);

        // Wait for completion of the job
        $isCompleted = $this->waitUntilRunCompleted($run->threadId, $run->id);
        if (!$isCompleted) {
            throw new \Exception('Failed to complete OpenAI job');
        }

        // Retrieve and handle response
        $response = $this->handleOpenAIResponse($run->threadId);

        return response()->json(['message' => $response]);
    }

    private function handleOpenAIResponse($threadId)
    {
        // Retrieve messages from the thread
        $messages = $this->client->threads()->messages()->list($threadId, [
            'order' => 'desc',
            'limit' => 1,
        ]);

        foreach ($messages->data as $message) {
            foreach ($message->content as $content) {
                if ($content->type === 'text') {
                    $messageText = $content->text->value;

                    // Check if it's a 'bye' message
                    if (strtolower($messageText) === 'bye') {
                        return '会話を終了しました。';
                    }

                    // Check if it's a 'complete' message
                    if (strtolower($messageText) === '対話を完了') {
                        // Update conversation and save summary
                        $conversationId = $message->conversation_id;
                        $conversation = Conversation::findOrFail($conversationId);
                        $this->saveSummary($conversation);
                        return '会話を完了しました。';
                    }

                    return $messageText;
                }
            }
        }

        return 'No response from OpenAI';
    }

    private function saveSummary($conversation)
    {
        // Generate summary here (example: concatenate messages)
        $summaryMessage = $this->generateSummary($conversation);

        // Save summary to ConversationMessagesTable
        $summary = new ConversationMessage();
        $summary->conversation_id = $conversation->id;
        $summary->message = $summaryMessage;
        $summary->role_id = 2; // Assuming this is the agent role
        $summary->summary = true; // Flag for summary
        $summary->save();

        // Update agent_status to 'reacted'
        $conversation->agent_status = 'reacted';
        $conversation->save();
    }

    private function generateSummary($conversation)
    {
        // Implement logic to generate summary from conversation messages
        return 'Summary generated here';
    }

    private function waitUntilRunCompleted($threadId, $runId)
    {
        $count = 0;
        do {
            sleep(3);
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
            if ($run->status === 'completed') {
                return true;
            }
            $count++;
        } while ($count < 5);
        return false;
    }
}
