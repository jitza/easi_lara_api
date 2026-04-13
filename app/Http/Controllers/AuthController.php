<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = strtolower(explode('@', $request->username)[0]);
        $password = $request->password;

        if (!$this->ldapAuthenticate($username, $password) &&
            !$this->pgsqlAuthenticate($username, $password)) {
            
            return response()->json([
                'success' => false,
                'message' => 'Mismatch username/password'
            ], 401);
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in system'
            ], 404);
        }

        if ($user->status === 'Disabled') {
            return response()->json([
                'success' => false,
                'message' => 'Account disabled'
            ], 403);
        }

        if ($user->activeUntil && now()->gt($user->activeUntil)) {
            return response()->json([
                'success' => false,
                'message' => 'Account expired'
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }
    private function ldapAuthenticate($username, $password)
    {
        return false; // placeholder for now
    }

    private function pgsqlAuthenticate($username, $password)
    {
        return false; // placeholder for now
    }

    
}

