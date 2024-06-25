<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザーIDへの外部キー
            $table->text('summary'); // 結果サマリ
            $table->float('serotonin'); // セロトニンの値
            $table->float('dopamine'); // ドーパミンの値
            $table->float('oxytocin'); // オキシトシンの値
            $table->text('recommendations'); // おすすめの回復方法
            $table->string('pdf_path'); // 結果サマリ
            $table->timestamps(); // created_at と updated_at を自動追加
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
