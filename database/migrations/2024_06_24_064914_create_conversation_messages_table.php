<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade'); // 会話IDへの外部キー
            $table->text('message'); // メッセージ本文
            $table->boolean('is_hidden')->default(false); // メッセージが隠されているかどうか
            $table->TinyInt('role_id');
            $table->timestamps(); // created_at と updated_at を自動追加
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_messages');
    }
}
