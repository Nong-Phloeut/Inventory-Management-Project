<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Step 1: Validate input
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|email',
        //     'password' => 'required|min:6',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => 'validation_error',
        //         'errors' => $validator->errors()
        //     ], 422);
        // }

        // Step 2: Attempt login
        $credentials = $request->only('email', 'password');
        // dd($credentials);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'invalid_credentials',
                'message' => 'Email or password is incorrect'
            ], 401);
        }
        // Get the logged-in user
        $user = Auth::user();
        // Create audit log manually
        AuditLog::create([
            'user_id' => $user->id,
            'action_type' => 'login',
            'module' => 'Auth',
            'description' => 'User logged in',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'old_values' => null,
            'new_values' => null,
        ]);
        // Step 3: Success
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me()
    {
        return response()->json(Auth::user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function refresh()
    {
        return response()->json([
            'token' => JWTAuth::refresh(JWTAuth::getToken())
        ]);
    }
}
