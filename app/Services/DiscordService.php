<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DiscordService
{
    protected string $botUrl;

    public function __construct()
    {
        $this->botUrl = env('DISCORD_BOT_API_URL', 'http://localhost:3000');
    }

    /**
     * Fetch the user's guilds from Discord API.
     */
    public function getUserGuilds(string $accessToken): array
    {
        $cacheKey = 'discord_user_guilds_' . md5($accessToken);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withToken($accessToken)
                ->get('https://discord.com/api/users/@me/guilds');

            if ($response->successful()) {
                $guilds = $response->json();
                if (is_array($guilds) && !empty($guilds)) {
                    // Cache the guilds for 60 seconds
                    Cache::put($cacheKey, $guilds, 60);
                    return $guilds;
                }
            }

            Log::error('Failed to fetch user guilds from Discord API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Exception while fetching user guilds', ['message' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Get the list of guilds the bot is currently in.
     */
    public function getBotGuilds(): array
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->botUrl}/api/guilds");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Could not connect to Discord bot Express API. Is the bot running?', ['message' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Get status of a guild player (playing, queue, volume, etc.)
     */
    public function getGuildStatus(string $guildId): array
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->botUrl}/api/guilds/{$guildId}/status");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning("Could not fetch status for guild {$guildId} from Discord bot", ['message' => $e->getMessage()]);
        }

        return [
            'connected' => false,
            'playing' => false,
            'currentTrack' => null,
            'queue' => [],
            'volume' => 50,
            'playbackState' => 'idle'
        ];
    }

    /**
     * Send control command to the bot.
     */
    public function sendControl(string $guildId, string $action, array $data = []): array
    {
        try {
            $response = Http::timeout(5)
                ->post("{$this->botUrl}/api/guilds/{$guildId}/{$action}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => $response->json('error') ?? 'Request failed with status ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error("Error sending action {$action} to guild {$guildId}", ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Could not connect to Discord bot. Please make sure the bot daemon is running.'
            ];
        }
    }
}
