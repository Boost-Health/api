<?php

use App\Enums\UserType;
use App\Models\User;
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
        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->references('id')->on('users')->index();
            $table->string('name')->nullable()->default('bot');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        $user = User::updateOrCreate(['type' => UserType::BOT], [
            'first_name' => 'Bot',
            'last_name' => 'Man',
            'email' => 'boosthealthlimited@gmail.com',
            'type' => UserType::BOT,
        ]);

        $user->bot()->create(['name' => 'Bot', 'meta' => ['llm' => 'GPT-4o']]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_users');
    }
};
