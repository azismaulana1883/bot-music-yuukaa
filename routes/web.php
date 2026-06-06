<?php

use App\Http\Controllers\DiscordAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// OAuth Routes
Route::get('/login/discord', [DiscordAuthController::class, 'redirect'])->name('login.discord');
Route::get('/login', [DiscordAuthController::class, 'redirect'])->name('login');
Route::get('/login/discord/callback', [DiscordAuthController::class, 'callback']);
Route::post('/logout', [DiscordAuthController::class, 'logout'])->name('logout');

// Dashboard Routes (Authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/guild/{guild_id}', [DashboardController::class, 'guild'])->name('dashboard.guild');
    Route::get('/dashboard/guild/{guild_id}/status', [DashboardController::class, 'status'])->name('dashboard.guild.status');
    Route::post('/dashboard/guild/{guild_id}/control', [DashboardController::class, 'control'])->name('dashboard.guild.control');
});

// Yuukaa Public Music Player Routes
Route::get('/music-play', function () {
    return view('music-play');
})->name('music.play');

Route::get('/api/music/search', function (\Illuminate\Http\Request $request) {
    $query = $request->query('query');
    if (!$query) {
        return response()->json(['success' => false, 'error' => 'Query is required'], 400);
    }
    
    $botUrl = env('DISCORD_BOT_API_URL', 'http://localhost:3000');
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(5)
            ->get("{$botUrl}/api/search", ['query' => $query]);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['success' => false, 'error' => 'Search failed on bot side'], 500);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => 'Bot is not running or unreachable'], 500);
    }
});
