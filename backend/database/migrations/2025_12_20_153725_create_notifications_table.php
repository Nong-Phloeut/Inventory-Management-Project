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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('channel', ['system', 'telegram', 'email', 'push'])
                ->default('system');

            $table->string('title');
            $table->text('message')->nullable();

            $table->string('type')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->json('data')->nullable();

            // Telegram
            $table->string('telegram_chat_id')->nullable();
            $table->boolean('sent_to_telegram')->default(false);
            $table->timestamp('telegram_sent_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
