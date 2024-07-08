<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('conversations', function (Blueprint $table) {
        $table->enum('agent_status', [
            'thinking',
            'reacted',
        ])->default('thinking');
    });
}

    public function down(): void
    {
    Schema::table('conversations', function (Blueprint $table) {
        $table->dropColumn('agent_status');
    });
    }
};
