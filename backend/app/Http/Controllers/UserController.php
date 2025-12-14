<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    // LIST ALL USERS
    public function index()
    {
        $users = User::with('role')->get();
        return response()->json($users, 200);
    }

    // CREATE USER
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'status'    => $request->status,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->load('role')
        ], 201);
    }

    // SHOW SINGLE USER
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return response()->json($user, 200);
    }

    // UPDATE USER
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $data = [
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'username'  => $request->username,
            'role_id'   => $request->role_id,
            'status'    => $request->status,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->load('role')
        ], 200);
    }

    // DELETE USER
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
