<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $fbUser = Socialite::driver('facebook')->user();
            
            $user = User::where('email', $fbUser->email)
            ->first();

            if (!$user) {
                dd("User Not Found. Creating User...");
                $user = User::create([
                    'name' => $fbUser->name,
                    'email' => $fbUser->email,
                    'password' => bcrypt('1234'),
                ]);
            }
            echo "Done ! Generating token...";
            $token = $user->createToken('Facebook Token')->accessToken;
            
            return response()->json([
                'token' => $token,
                'user' => $user,
                'FBUser' => $fbUser
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}