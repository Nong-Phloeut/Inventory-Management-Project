<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
}
