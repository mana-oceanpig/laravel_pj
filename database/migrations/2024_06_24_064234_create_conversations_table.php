<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained()->onDelete('cascade'); // ユーザーIDへの外部キー
            $table->string('external_id')->nullable(); // 外部接続用のID
            $table->timestamps(); // created_at と updated_at を自動追加
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}