<?php

namespace App\Http\Controllers;

use App\Services\DiscordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DiscordService $discord;

    public function __construct(DiscordService $discord)
    {
        $this->discord = $discord;
    }

    /**
     * Display the user's manageable Discord servers.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->discord_token) {
            return redirect()->route('login.discord');
        }

        // Get user's guilds
        $userGuilds = $this->discord->getUserGuilds($user->discord_token);
        
        // Filter guilds where user has Administrator (0x8) or Manage Guild (0x20) permissions
        $manageableGuilds = array_filter($userGuilds, function ($guild) {
            $permissions = isset($guild['permissions_new']) ? (int)$guild['permissions_new'] : (int)($guild['permissions'] ?? 0);
            return ($permissions & 0x8) === 0x8 || ($permissions & 0x20) === 0x20;
        });

        // Get guilds where the bot is present
        $botGuilds = $this->discord->getBotGuilds();

        // Map guilds to check bot presence
        $guilds = array_map(function ($guild) use ($botGuilds) {
            $guild['has_bot'] = in_array($guild['id'], $botGuilds);
            return $guild;
        }, $manageableGuilds);

        // Sort: guilds with bot first
        usort($guilds, function ($a, $b) {
            return $b['has_bot'] <=> $a['has_bot'];
        });

        return view('dashboard', compact('guilds'));
    }

    /**
     * Show the control panel for a specific guild.
     */
    public function guild(string $guildId)
    {
        $user = Auth::user();
        if (!$user || !$user->discord_token) {
            return redirect()->route('login.discord');
        }
        
        // Verify user manages this guild
        $userGuilds = $this->discord->getUserGuilds($user->discord_token);
        $guildDetails = null;
        
        foreach ($userGuilds as $guild) {
            if ($guild['id'] === $guildId) {
                $permissions = isset($guild['permissions_new']) ? (int)$guild['permissions_new'] : (int)($guild['permissions'] ?? 0);
                if (($permissions & 0x8) === 0x8 || ($permissions & 0x20) === 0x20) {
                    $guildDetails = $guild;
                    break;
                }
            }
        }

        if (!$guildDetails) {
            abort(403, 'You do not have administrative access to this Discord server.');
        }

        // Get status from bot
        $status = $this->discord->getGuildStatus($guildId);

        // Generate Bot Invite Link
        $clientId = env('DISCORD_CLIENT_ID');
        $botInviteUrl = "https://discord.com/oauth2/authorize?client_id={$clientId}&permissions=3145728&scope=bot%20applications.commands&guild_id={$guildId}&disable_guild_select=true";

        return view('guild-control', compact('guildDetails', 'status', 'botInviteUrl'));
    }

    /**
     * Fetch the raw status for real-time visual updates.
     */
    public function status(string $guildId)
    {
        $user = Auth::user();
        if (!$user || !$user->discord_token) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        
        // Verify user manages this guild
        $userGuilds = $this->discord->getUserGuilds($user->discord_token);
        $authorized = false;
        
        foreach ($userGuilds as $guild) {
            if ($guild['id'] === $guildId) {
                $permissions = isset($guild['permissions_new']) ? (int)$guild['permissions_new'] : (int)($guild['permissions'] ?? 0);
                if (($permissions & 0x8) === 0x8 || ($permissions & 0x20) === 0x20) {
                    $authorized = true;
                    break;
                }
            }
        }

        if (!$authorized) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $status = $this->discord->getGuildStatus($guildId);
        return response()->json($status);
    }

    /**
     * Send command to the bot.
     */
    public function control(string $guildId, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->discord_token) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        
        // Verify user manages this guild
        $userGuilds = $this->discord->getUserGuilds($user->discord_token);
        $authorized = false;
        
        foreach ($userGuilds as $guild) {
            if ($guild['id'] === $guildId) {
                $permissions = isset($guild['permissions_new']) ? (int)$guild['permissions_new'] : (int)($guild['permissions'] ?? 0);
                if (($permissions & 0x8) === 0x8 || ($permissions & 0x20) === 0x20) {
                    $authorized = true;
                    break;
                }
            }
        }

        if (!$authorized) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $action = $request->input('action');
        $data = $request->except('action');
        
        // Include user details so the bot knows who requested it
        $data['userId'] = $user->discord_id;
        $data['username'] = $user->name;

        $response = $this->discord->sendControl($guildId, $action, $data);

        return response()->json($response);
    }
}
