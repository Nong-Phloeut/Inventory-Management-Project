<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TelegramController extends Controller
{
    public function generateLinkToken(Request $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Generate a random 8-character token
        $token = Str::random(8);
        $user->telegram_link_token = $token;
        $user->save();

        return response()->json([
            'token' => $token,
            'message' => 'Use this token in Telegram bot to link your account'
        ]);
    }

    //
    public function webhook(Request $request)
    {
        $message = $request->input('message');
        if (!$message) {
            return response()->json(['status' => 'no message']);
        }

        $chatId = $message['chat']['id'];
        $text   = $message['text'] ?? '';

        // Handle /start command
        if ($text === '/start') {
            // Save temporarily in pending table
            DB::table('telegram_pending_links')->updateOrInsert(
                ['chat_id' => $chatId],
                ['created_at' => now()]
            );

            $this->sendMessage($chatId, '✅ Telegram started. Please return to the system to finish linking.');
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Send message to Telegram user
     */
    // private function sendMessage($chatId, $text)
    // {
    //     $token = config('services.telegram.bot_token'); // or hardcode if needed

    //     Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
    //         'chat_id' => $chatId,
    //         'text'    => $text
    //     ]);
    // }

    private function sendMessage($chatId, $text)
    {
        $botToken = "8580655335:AAG2XhuPJjW7zQMjikd8ZFpqkqPvjSeTBD8";
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * Link Telegram chat_id to logged-in user
     */
    public function linkTelegram(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user(); // Works with JWT

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Check if user already linked
        if ($user->telegram_chat_id) {
            return response()->json([
                'message' => 'Telegram already linked.'
            ], 200);
        }

        // Get latest pending chat_id
        $pending = DB::table('telegram_pending_links')->latest()->first();

        if (!$pending) {
            return response()->json([
                'message' => 'No Telegram start found. Please click Start in Telegram first.'
            ], 400);
        }

        $this->sendMessage($user->telegram_chat_id,'Telegram linked successfully!');
        // Save chat_id in users table
        $user->telegram_chat_id = $pending->chat_id;
        $user->save();

        // Delete pending row
        DB::table('telegram_pending_links')->where('chat_id', $pending->chat_id)->delete();

        return response()->json([
            'message' => 'Telegram linked successfully!'
        ]);
    }
}
