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
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->references('id')->on('users');
            $table->string('telegram_id')->index();
            $table->string('telegram_chat_id');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'telegram_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
};
