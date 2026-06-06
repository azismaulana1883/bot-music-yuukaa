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
