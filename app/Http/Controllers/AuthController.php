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
            
            $user = User::firstOrCreate(
                ['facebook_id' => $fbUser->id],
                [
                    'name' => $fbUser->name,
                ]
            );

            $token = $user->createToken('Facebook Token')->accessToken;
            
            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}