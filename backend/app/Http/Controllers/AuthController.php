<?php

namespace App\Http\Controllers;

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

        // Step 3: Success
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => Auth::user()
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
