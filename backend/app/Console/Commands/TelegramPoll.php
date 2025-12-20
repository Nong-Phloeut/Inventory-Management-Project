<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class TelegramPoll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:telegram-poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Telegram bot for messages';

    /**
     * Execute the console command.
     */
    private $botToken = '8580655335:AAG2XhuPJjW7zQMjikd8ZFpqkqPvjSeTBD8';
    private $offset = 0; // track last update_id

    public function handle()
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
        $response = Http::get($url, ['offset' => $this->offset + 1]);

        $updates = $response->json()['result'] ?? [];

        foreach ($updates as $update) {
            $this->offset = $update['update_id'];

            if (!isset($update['message'])) continue;

            $chatId = $update['message']['chat']['id'];
            $text   = $update['message']['text'] ?? '';

            if (str_starts_with($text, '/start')) {
                $parts = explode(' ', $text);
                $token = $parts[1] ?? null;

                if ($token) {
                    $user = User::where('telegram_link_token', $token)->first();
                    if ($user) {
                        $user->telegram_chat_id = $chatId;
                        $user->save();
                        $this->sendMessage($chatId, "✅ Telegram linked for {$user->name}");
                    } else {
                        $this->sendMessage($chatId, "❌ Invalid token");
                    }
                } else {
                    $this->sendMessage($chatId, "Send /start <YOUR_TOKEN> to link your account.");
                }
            }
        }
    }

    private function sendMessage($chatId, $text)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

}
