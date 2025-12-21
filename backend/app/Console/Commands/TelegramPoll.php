<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Cache; // for storing last offset

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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve last offset from cache to avoid processing same update
        $lastOffset = Cache::get('telegram_offset', 0);

        $url = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
        $response = Http::get($url, [
            'offset' => $lastOffset + 1, // fetch updates after last processed
            'timeout' => 10,
        ]);

        $updates = $response->json()['result'] ?? [];

        foreach ($updates as $update) {
            $updateId = $update['update_id'] ?? null;
            if (!$updateId) continue;

            Cache::put('telegram_offset', $updateId, 3600);

            if (!isset($update['message'])) continue;

            $chatId = $update['message']['chat']['id'];
            $text   = $update['message']['text'] ?? '';

            $this->info("Received Telegram message: $text from chat_id $chatId");

            if (str_starts_with($text, '/start')) {
                $parts = explode(' ', $text);
                $token = $parts[1] ?? null;

                if ($token) {
                    $user = User::where('telegram_link_token', $token)->first();

                    if ($user) {
                        $user->telegram_chat_id = $chatId;
                        $user->save();

                        $this->info("✅ Telegram linked for {$user->name} (chat_id: $chatId)");
                        $this->sendMessage($chatId, "✅ Telegram linked successfully for {$user->name}!");
                    } else {
                        $this->info("❌ Invalid token: $token from chat_id $chatId");
                        $this->sendMessage($chatId, "❌ Invalid token. Please generate a valid token in your profile.");
                    }
                } else {
                    $this->sendMessage($chatId, "Send /start <YOUR_TOKEN> to link your account.");
                }
            }
        }


        $this->info("✅ Telegram poll executed. Updates processed: " . count($updates));
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
