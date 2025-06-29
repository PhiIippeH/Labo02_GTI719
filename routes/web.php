<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    // Use Passport to issue a token for the authenticated user
    $user = \App\Models\User::firstOrCreate(
        ['email' => $googleUser->getEmail()],
        ['name' => $googleUser->getName()]
    );

    $token = $user->createToken('Google OAuth Token')->accessToken;

    return response()->json(['token' => $token]);
});

// Redirect to idperso for login
Route::get('/auth/idperso', function () {
    $query = http_build_query([
        'client_id' => config('services.idperso.client_id'),
        'redirect_uri' => route('idperso.redirect'),
        'response_type' => 'code',
        'scope' => 'openid profile email',
        'state' => csrf_token(),
    ]);
    print($query);
    return redirect("http://localhost:8001/authorize?$query");
});

Route::get('/auth/idperso', [AuthController::class, 'redirectToIdPerso']);
Route::get('/auth/idperso/callback', [AuthController::class, 'handleIdPersoCallback'])->name('idperso.callback');

Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
