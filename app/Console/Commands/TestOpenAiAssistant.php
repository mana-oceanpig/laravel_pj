<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI;
use OpenAI\Client;

class TestOpenAiAssistant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:open-ai-assistant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command to check how to communicate with OpenAI Assistant service';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->output->info('Test of OpenAI Assistant service');

        // generating client instance
        $secretKey = env('OPEN_AI_SECRET_KEY');
        $client = OpenAI::client($secretKey);

        // retrieved info of predefined assistant
        $assistantId = env('OPEN_AI_ASSISTANT_ID');
        $response = $client->assistants()->retrieve($assistantId);
        $this->line("Successfully retrieved OpenAI Assistant info, name:{$response->name}");

        // creating a thread and ask to run job
        $run = $client->threads()->createAndRun([
            'assistant_id' => $assistantId,
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => '「今日はどうしましたか？」と私に話しかけてください。',
                    ],
                ],
            ],
        ]);
        $this->line('Created the thread and sent a message to the agent');

        // waiting until the job has been done
        $isCompleted = $this->waitUntilRunCompleted($client, $run->threadId, $run->id);
        if (! $isCompleted) {
            $this->output->error('Failed to complete run, abort!');
            exit();
        }
        $messages = $client->threads()->messages()->list(
            $run->threadId,
            [
                'order' => 'desc',
                'limit' => 1,
            ]
        );
        foreach ($messages->data as $message) {
            foreach ($message->content as $content) {
                if ($content['text'] !== null) {
                    $this->info($content['text']['value']);
                }
            }
        }
        $threadId = $run->threadId;

        // communications between the user and the agent
        while (true) {

            // user message
            $message = $this->output->ask('Enter message. To quit type "bye".');
            if ($message === 'bye') {
                break;
            }

            // processing and sending
            $client->threads()->messages()->create($threadId, [
                'content' => $message,
                'role' => 'user',
            ]);
            $run = $client->threads()->runs()->create($threadId, [
                'assistant_id' => $assistantId,
            ]);

            // waiting until the job has been done
            $isCompleted = $this->waitUntilRunCompleted($client, $threadId, $run->id);
            if (! $isCompleted) {
                $this->output->error('Failed to complete run, abort!');
                exit();
            }

            // fetching message
            $messages = $client->threads()->messages()->list(
                $threadId,
                [
                    'order' => 'desc',
                    'limit' => 1,
                ]
            );
            foreach ($messages->data as $message) {
                foreach ($message->content as $content) {
                    if ($content['text'] !== null) {
                        $this->info($content['text']['value']);
                    }
                }
            }
        }

        // asking the agent to summarize
        $client->threads()->messages()->create($threadId, [
            'content' => '今までの会話の内容をビジネスライクに要約してください',
            'role' => 'user',
        ]);
        $run = $client->threads()->runs()->create($threadId, [
            'assistant_id' => $assistantId,
        ]);
        $this->line('Generating summary of the conversation');

        // waiting until the job has been done
        $isCompleted = $this->waitUntilRunCompleted($client, $threadId, $run->id);
        if (! $isCompleted) {
            $this->output->error('Failed to complete run, abort!');
            exit();
        }

        // fetching message
        $messages = $client->threads()->messages()->list(
            $threadId,
            [
                'order' => 'desc',
                'limit' => 1,
            ]
        );
        foreach ($messages->data as $message) {
            foreach ($message->content as $content) {
                if ($content['text'] !== null) {
                    $this->output->success($content['text']['value']);
                }
            }
        }
    }

    private function waitUntilRunCompleted(Client $client, string $threadId, string $runId): bool
    {
        $count = 0;
        do {
            sleep(3);
            $run = $client->threads()->runs()->retrieve($threadId, $runId);
            if ($run->status === 'completed') {
                return true;
            }
            $count++;
        } while ($count < 5);

        return false;
    }
}
