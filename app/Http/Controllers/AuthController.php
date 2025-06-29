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
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function redirectToIdPerso()
    {
        $query = http_build_query([
            'client_id' => config('services.idperso.client_id'),
            'redirect_uri' => route('idperso.callback'),
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);
        return redirect(config('services.idperso.base_uri') . '/authorize?' . $query);
    }

    public function handleIdPersoCallback(Request $request)
    {
        $code = $request->input('code');

        $response = Http::asForm()->post(config('services.idperso.base_uri') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.idperso.client_id'),
            'client_secret' => config('services.idperso.client_secret'),
            'redirect_uri' => route('idperso.callback'),
            'code' => $code,
        ]);

        $token = $response->json('access_token');

        $userInfo = Http::withToken($token)
            ->get(config('services.idperso.base_uri') . '/userinfo')
            ->json();

        $user = User::firstOrCreate(
            ['email' => $userInfo['email']],
            ['name' => $userInfo['name']]
        );

        auth()->login($user);

        return redirect('/');
    }

}
