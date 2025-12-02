<?php

// app/Http/Controllers/SocialiteController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            // Get user information from the provider (e.g., Google)
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Authentication failed.']);
        }

        // Find user by Google ID or by email
        $user = User::where('provider_id', $socialiteUser->getId())
                    ->where('provider', $provider)
                    ->first();

        // If user exists, log them in
        if ($user) {
            Auth::login($user, true);
            return redirect('/dashboard'); // Change to your desired post-login route
        }

        // Check if a user with the same email already exists
        $existingUser = User::where('email', $socialiteUser->getEmail())->first();

        if ($existingUser) {
            // If email exists, link the social account and log them in
            $existingUser->provider = $provider;
            $existingUser->provider_id = $socialiteUser->getId();
            $existingUser->save();
            Auth::login($existingUser, true);
            return redirect('/dashboard');
        }

        // If user doesn't exist, create a new one
        $newUser = User::create([
            'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname(),
            'email' => $socialiteUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            // Socialite doesn't require a password, but the column is non-nullable.
            // We create a random hash or use a nullable password field in migration.
            // Assuming password is non-nullable:
            'password' => Hash::make(Str::random(10)),
        ]);

        Auth::login($newUser, true);
        return redirect('/dashboard');
    }
}
