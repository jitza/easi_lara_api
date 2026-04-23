<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Services\AuthService;
use Laravel\Socialite\Facades\Socialite;
use App\Models\UserAccountsModel;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            $user = $this->authService->authenticate(
                $request->username,
                $request->password
            );

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mismatch username/password'
                ], 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function redirectToMicrosoft()
    {
        // dd(Socialite::driver('microsoft'));
        return Socialite::driver('microsoft')
        ->with(['prompt' => 'select_account']) // or login to force login every time
        ->redirect();
        
    }

    public function handleMicrosoftCallback()
    {
        $microsoftUser = Socialite::driver('microsoft')->user();

        $email = $microsoftUser->getEmail();
        $username = strtolower(explode('@', $email)[0]);

        // Match user with your existing DB
        $user = UserAccountsModel::whereRaw('LOWER(username) = ?', [$username])->first();
 
        if (!$user) {
            // return redirect('http://localhost:9000/?error=user_not_found');
            return response()->json([
                'success' => false,
                'message' => 'User not found in system'
            ], 404);
        }

        // remove old tokens
        $user->tokens()->delete();

        // Create Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

       // Create temporary key
        $key = Str::random(40);

        // Store token for 1 minute
        Cache::put("login_$key", $token, now()->addSeconds(30));

        // Redirect WITH token
        return redirect("http://localhost:9000/auth/callback?key={$key}");
    }
    
    public function getToken($key)
    {
        $token = Cache::pull("login_$key"); // pull = get + delete //returns token

         //Bind key to IP and validate on retrieval (optional, for added security)
        //  Cache::put("login_$key", [
        //     'token' => $token,
        //     'ip' => request()->ip()
        // ], now()->addMinutes(1));

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Logout Successfully'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }

}