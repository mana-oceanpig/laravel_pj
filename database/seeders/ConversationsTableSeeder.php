<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;
use Carbon\Carbon;

class ConversationsTableSeeder extends Seeder
{
    public function run()
    {
        // シードデータを挿入
        $conversations = [
            [
                'user_id' => 1,
                'external_id' => 'ext_1',
                'status' => Conversation::STATUS_IN_PROGRESS,
                'last_activity_at' => Carbon::now()->subMinutes(10),
                'created_at' => Carbon::now()->subMinutes(20),
                'updated_at' => Carbon::now()->subMinutes(10),
            ],
            [
                'user_id' => 2,
                'external_id' => 'ext_2',
                'status' => Conversation::STATUS_COMPLETED,
                'last_activity_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($conversations as $conversation) {
            Conversation::create($conversation);
        }
    }
}
