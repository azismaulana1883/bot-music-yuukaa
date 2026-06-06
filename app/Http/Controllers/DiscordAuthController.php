<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class DiscordAuthController extends Controller
{
    /**
     * Redirect the user to the Discord authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('discord')
            ->scopes(['identify', 'guilds'])
            ->redirect();
    }

    /**
     * Obtain the user information from Discord.
     */
    public function callback()
    {
        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Authentication failed: ' . $e->getMessage());
        }

        // Find or create user
        $user = User::where('discord_id', $discordUser->id)
            ->orWhere('email', $discordUser->email)
            ->first();

        $expiresIn = $discordUser->expiresIn ?? 604800;

        if ($user) {
            $user->update([
                'name' => $discordUser->name ?? $discordUser->nickname,
                'email' => $discordUser->email ?? ($discordUser->id . '@discord.com'),
                'discord_id' => $discordUser->id,
                'avatar' => $discordUser->avatar,
                'discord_token' => $discordUser->token,
                'discord_refresh_token' => $discordUser->refreshToken,
                'discord_token_expires_at' => now()->addSeconds($expiresIn),
            ]);
        } else {
            $user = User::create([
                'name' => $discordUser->name ?? $discordUser->nickname,
                'email' => $discordUser->email ?? ($discordUser->id . '@discord.com'),
                'password' => null, // Password is null for OAuth
                'discord_id' => $discordUser->id,
                'avatar' => $discordUser->avatar,
                'discord_token' => $discordUser->token,
                'discord_refresh_token' => $discordUser->refreshToken,
                'discord_token_expires_at' => now()->addSeconds($expiresIn),
            ]);
        }

        Auth::login($user, true);

        return redirect('/dashboard');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
