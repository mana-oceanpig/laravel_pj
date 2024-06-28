<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class OpenAIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('OPENAI_API_KEY');

        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key is not set.');
        }
    }

    public function summarizeText($text)
    {
        if (strlen($text) <= 100) {
            return $text;
        }

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Please summarize the following text in 50 words or less.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ],
                ],
                'max_tokens' => 50,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['error'])) {
            throw new Exception($data['error']['message']);
        }

        return trim($data['choices'][0]['message']['content']) ?? $text;
    }

    public function generateResponse($text)
    {
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Respond with empathy and understanding within 50 characters.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ],
                ],
                'max_tokens' => 50,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['error'])) {
            throw new Exception($data['error']['message']);
        }

        return trim($data['choices'][0]['message']['content']) ?? "I'm sorry, I couldn't generate a response.";
    }
}
