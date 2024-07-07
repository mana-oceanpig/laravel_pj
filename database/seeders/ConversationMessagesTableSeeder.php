<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConversationMessage;
use Carbon\Carbon;

class ConversationMessagesTableSeeder extends Seeder
{
    public function run()
    {
        // シードデータを挿入
        $messages = [
            [
                'conversation_id' => 1,
                'external_id' => 'msg_1',
                'message' => 'こんにちは！',
                'summary' => '挨拶',
                'is_hidden' => false,
                'role_id' => 1,
                'created_at' => Carbon::now()->subMinutes(15),
                'updated_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'conversation_id' => 1,
                'external_id' => 'msg_2',
                'message' => 'こんにちは、お元気ですか？',
                'summary' => '返事',
                'is_hidden' => false,
                'role_id' => 2,
                'created_at' => Carbon::now()->subMinutes(10),
                'updated_at' => Carbon::now()->subMinutes(10),
            ],
            [
                'conversation_id' => 2,
                'external_id' => 'msg_3',
                'message' => '昨日の会話を振り返りたい。',
                'summary' => '振り返り',
                'is_hidden' => false,
                'role_id' => 1,
                'created_at' => Carbon::now()->subDays(1)->subMinutes(30),
                'updated_at' => Carbon::now()->subDays(1)->subMinutes(30),
            ],
        ];

        foreach ($messages as $message) {
            ConversationMessage::create($message);
        }
    }
}
