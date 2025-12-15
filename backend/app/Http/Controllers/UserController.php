<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
class UserController extends Controller
{
    // LIST ALL USERS
    public function index(Request $request)
    {
        $query = User::query();

        // Keyword search
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('email', 'like', "%{$keyword}%")
                ->orWhere('username', 'like', "%{$keyword}%");
            });
        }

        // Roles filter (use role_id column)
        if ($request->filled('roles')) {
            $roles = explode(',', $request->roles);
            $query->whereIn('role_id', $roles);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Eager load role
        $users = $query->with('role')->get();

        return response()->json($users, 200);
    }

    // CREATE USER
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
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
            'name' => $request->name,
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

            // Protect admin users
            if ($user->role && $user->role->slug === 'admin') {
                  return response()->json([
                        'status' => 'error',
                        'message' => 'Admin user cannot be deleted'
                  ], 403);
            }

            $user->delete();

            return response()->json([
                  'status' => 'success',
                  'message' => 'User deleted successfully'
            ]);
      }

}
