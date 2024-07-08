<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;

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

        // Retrieve and return the response message
        $messages = $this->client->threads()->messages()->list(
            $run->threadId,
            [
                'order' => 'desc',
                'limit' => 1,
            ]
        );

        foreach ($messages->data as $message) {
            foreach ($message->content as $content) {
                if ($content->type === 'text') {
                    return response()->json(['message' => $content->text->value]);
                }
            }
        }

        return response()->json(['message' => 'No response from OpenAI']);
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