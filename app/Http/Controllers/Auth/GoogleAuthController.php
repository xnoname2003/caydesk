<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('google_social_auth_id', $googleUser->id)->first();

            if (!$user) {
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    $user->update([
                        'google_social_auth_id' => $googleUser->id,
                        'google_social_auth_type' => 'google',
                    ]);
                } else {
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'password' => Hash::make(Str::random(16)),
                        'google_social_auth_id' => $googleUser->id,
                        'google_social_auth_type' => 'google',
                    ]);

                    $user->assignRole('customer');
                }
            }

            Auth::login($user);

            return redirect()->intended('/app');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Failed to authenticate with Google. Please try again.']);
        }
    }
}
