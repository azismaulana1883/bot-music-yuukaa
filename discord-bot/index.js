import { Client, GatewayIntentBits, MessageFlags, REST, Routes, SlashCommandBuilder } from 'discord.js';
import { 
    joinVoiceChannel, 
    createAudioPlayer, 
    createAudioResource, 
    AudioPlayerStatus, 
    VoiceConnectionStatus,
    entersState,
    StreamType,
    getVoiceConnection
} from '@discordjs/voice';
import play from 'play-dl';
import express from 'express';
import dotenv from 'dotenv';
import path from 'path';
import fs from 'fs';
import YTDlpWrap from 'yt-dlp-wrap';
import ffmpeg from 'ffmpeg-static';
import { spawn } from 'child_process';

// Set FFMPEG_PATH environment variable so prism-media knows where to find it
process.env.FFMPEG_PATH = ffmpeg;


// Load environment variables. Check both current directory and parent directory.
if (fs.existsSync('.env')) {
    dotenv.config({ path: '.env' });
} else if (fs.existsSync('../.env')) {
    dotenv.config({ path: '../.env' });
} else {
    dotenv.config({ path: path.join(path.resolve(), '.env') });
}

// Check and download yt-dlp binary
const ytdlpPath = path.join(path.resolve(), 'yt-dlp.exe');
if (!fs.existsSync(ytdlpPath)) {
    console.log("Downloading yt-dlp binary...");
    YTDlpWrap.default.downloadFromGithub(ytdlpPath)
        .then(() => console.log("yt-dlp binary downloaded successfully."))
        .catch(err => console.error("Failed to download yt-dlp:", err.message));
}
const YTDlp = YTDlpWrap.default || YTDlpWrap;
const ytdlp = new YTDlp(ytdlpPath);

async function getStreamUrl(videoUrl) {
    const stdout = await ytdlp.execPromise([
        videoUrl,
        '-f', 'bestaudio',
        '--get-url'
    ]);
    return stdout.trim();
}

const TOKEN = process.env.DISCORD_BOT_TOKEN;
const CLIENT_ID = process.env.DISCORD_CLIENT_ID;
const PORT = process.env.DISCORD_BOT_PORT || 3000;

if (!TOKEN || !CLIENT_ID) {
    console.error('Error: DISCORD_BOT_TOKEN or DISCORD_CLIENT_ID is missing in the .env file.');
    process.exit(1);
}

// Configure play-dl Spotify integration if credentials are present
const spotifyId = process.env.SPOTIFY_CLIENT_ID;
const spotifySecret = process.env.SPOTIFY_CLIENT_SECRET;
if (spotifyId && spotifySecret) {
    try {
        play.setToken({
            spotify: {
                client_id: spotifyId,
                client_secret: spotifySecret,
                market: 'ID'
            }
        });
        console.log('Spotify API credentials configured for play-dl.');
    } catch (err) {
        console.warn('Could not initialize Spotify token for play-dl:', err.message);
    }
}

// Initialize Discord Client
const client = new Client({
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildVoiceStates,
        GatewayIntentBits.GuildMessages
    ]
});

// Guild Players Map
// guildId => { connection, player, queue: [], history: [], currentTrack: null, volume: 0.5, status: 'idle', textChannel: null }
const guildPlayers = new Map();

// Helper to get or create a guild player
function getOrCreatePlayer(guildId) {
    if (guildPlayers.has(guildId)) {
        return guildPlayers.get(guildId);
    }

    const audioPlayer = createAudioPlayer();
    const playerState = {
        connection: null,
        audioPlayer: audioPlayer,
        queue: [],
        history: [],       // tracks that have been played (max 20)
        currentTrack: null,
        volume: 0.5,
        status: 'idle',
        textChannel: null,
        isTransitioning: false
    };

    // Set up audio player event listeners
    audioPlayer.on(AudioPlayerStatus.Idle, () => {
        const state = guildPlayers.get(guildId);
        if (state?.isTransitioning) {
            state.isTransitioning = false;
            console.log(`Skipping auto-advance for manual transition in guild ${guildId}.`);
            return;
        }

        console.log(`Audio player idle in guild ${guildId}. Checking queue...`);
        playNext(guildId);
    });

    audioPlayer.on('error', error => {
        console.error(`Audio player error in guild ${guildId}:`, error.message);
        playNext(guildId);
    });

    audioPlayer.on('stateChange', (oldState, newState) => {
        console.log(`Audio player state transition from ${oldState.status} to ${newState.status} in guild ${guildId}`);
    });


    guildPlayers.set(guildId, playerState);
    return playerState;
}

function cleanupTrackProcesses(track) {
    if (!track) return;

    if (track.processes?.ytdlpProc && track.processes?.ffmpegProc) {
        try {
            track.processes.ytdlpProc.stdout.unpipe(track.processes.ffmpegProc.stdin);
        } catch (err) {
            console.warn('Failed to unpipe stdout from stdin:', err.message);
        }
    }

    try {
        if (track.processes?.ffmpegProc) {
            track.processes.ffmpegProc.stdin?.destroy();
            track.processes.ffmpegProc.kill('SIGKILL');
        }
    } catch (err) {
        console.warn('Failed to kill ffmpeg process:', err.message);
    }

    try {
        if (track.processes?.ytdlpProc) {
            track.processes.ytdlpProc.kill('SIGKILL');
        }
    } catch (err) {
        console.warn('Failed to kill yt-dlp process:', err.message);
    }

    try {
        if (track.resource?.playStream) {
            track.resource.playStream.destroy();
        }
    } catch (err) {
        console.warn('Failed to destroy audio stream:', err.message);
    }

    try {
        if (track.resource) {
            track.resource = null;
        }
    } catch {}
}

// Play next song in the queue
async function playNext(guildId) {
    const state = guildPlayers.get(guildId);
    if (!state) return;

    cleanupTrackProcesses(state.currentTrack);

    if (state.audioPlayer && state.audioPlayer.state.status !== AudioPlayerStatus.Idle) {
        state.audioPlayer.stop(true);
    }

    if (state.queue.length === 0) {
        state.currentTrack = null;
        state.status = 'idle';
        console.log(`Queue is empty in guild ${guildId}. Player is idle.`);
        
        // Auto disconnect after 5 minutes of idle
        if (state.disconnectTimeout) clearTimeout(state.disconnectTimeout);
        state.disconnectTimeout = setTimeout(() => {
            if (state.queue.length === 0 && state.status === 'idle') {
                stopPlayer(guildId);
                console.log(`Disconnected from voice channel in guild ${guildId} due to inactivity.`);
            }
        }, 5 * 60 * 1000); // 5 minutes
        
        return;
    }

    if (state.disconnectTimeout) {
        clearTimeout(state.disconnectTimeout);
        state.disconnectTimeout = null;
    }

    // Save current track to history before moving on
    if (state.currentTrack) {
        state.history.push({ ...state.currentTrack, resource: undefined });
        if (state.history.length > 20) state.history.shift(); // keep max 20
    }

    const nextTrack = state.queue.shift();
    state.currentTrack = nextTrack;
    state.currentTrack.startedAt = Date.now();
    state.status = 'playing';

    try {
        console.log(`Playing in guild ${guildId}: ${nextTrack.title}`);
        let youtubeUrl = nextTrack.url;
        
        // Resolve Spotify url to YouTube URL
        if (nextTrack.source === 'spotify' || nextTrack.url.includes('spotify.com')) {
            const searchTitle = `${nextTrack.title} ${nextTrack.artist}`;
            const ytResults = await play.search(searchTitle, { limit: 1 });
            if (ytResults.length === 0) {
                throw new Error(`Could not find song matching Spotify track: ${searchTitle}`);
            }
            youtubeUrl = ytResults[0].url;
        }

        // Stream audio via yt-dlp → ffmpeg → Discord (stable full-length playback)
        console.log(`Streaming via yt-dlp+ffmpeg pipe: ${youtubeUrl}`);

        // Step 1: yt-dlp downloads best audio and pipes to stdout
        const ytdlpProc = spawn(ytdlpPath, [
            youtubeUrl,
            '-f', 'bestaudio/best',
            '--no-playlist',
            '-o', '-',
            '--quiet',
            '--no-warnings',
        ], { stdio: ['ignore', 'pipe', 'ignore'] });

        // Step 2: ffmpeg re-encodes to opus/pcm so @discordjs/voice can play it
        const ffmpegProc = spawn(ffmpeg, [
            '-i', 'pipe:0',          // read from stdin
            '-analyzeduration', '0',
            '-loglevel', 'quiet',
            '-f', 's16le',           // raw PCM signed 16-bit little-endian
            '-ar', '48000',          // 48kHz sample rate (Discord requires this)
            '-ac', '2',              // stereo
            'pipe:1'                 // output to stdout
        ], { stdio: ['pipe', 'pipe', 'ignore'] });

        // Pipe yt-dlp → ffmpeg
        ytdlpProc.stdout.pipe(ffmpegProc.stdin);

        // Handle stream-level pipe errors to prevent process crashes (EPIPE/ECONNRESET)
        ffmpegProc.stdin.on('error', err => {
            if (err.code !== 'EPIPE' && err.code !== 'ECONNRESET') {
                console.error(`ffmpeg stdin error in guild ${guildId}:`, err.message);
            }
        });
        ytdlpProc.stdout.on('error', err => {
            if (err.code !== 'EPIPE' && err.code !== 'ECONNRESET') {
                console.error(`yt-dlp stdout error in guild ${guildId}:`, err.message);
            }
        });
        ffmpegProc.stdout.on('error', err => {
            if (err.code !== 'EPIPE' && err.code !== 'ECONNRESET') {
                console.error(`ffmpeg stdout error in guild ${guildId}:`, err.message);
            }
        });

        // Handle yt-dlp errors
        ytdlpProc.on('error', err => console.error(`yt-dlp error in guild ${guildId}:`, err.message));
        ffmpegProc.on('error', err => console.error(`ffmpeg error in guild ${guildId}:`, err.message));

        // If yt-dlp exits early, close ffmpeg stdin so ffmpeg finishes cleanly
        ytdlpProc.on('close', code => {
            if (code !== 0) console.warn(`yt-dlp exited with code ${code} in guild ${guildId}`);
            try {
                ffmpegProc.stdin.end();
            } catch (err) {}
        });

        const resource = createAudioResource(ffmpegProc.stdout, {
            inputType: StreamType.Raw,   // Raw PCM s16le
            inlineVolume: true
        });

        resource.volume.setVolume(state.volume);
        resource.playStream.on('error', err => {
            console.error(`Audio stream error in guild ${guildId}:`, err.message);
        });

        state.audioPlayer.play(resource);
        state.currentTrack.resource = resource;
        state.currentTrack.processes = { ytdlpProc, ffmpegProc };

        if (state.textChannel) {
            state.textChannel.send(`🎶 **Sedang diputar:** [${nextTrack.title}](${nextTrack.url}) [Diminta oleh: ${nextTrack.requestedBy}]`);
        }
    } catch (error) {
        console.error(`Failed to play track in guild ${guildId}:`, error.message);
        cleanupTrackProcesses(state.currentTrack);
        if (state.textChannel) {
            state.textChannel.send(`❌ Gagal memutar lagu **${nextTrack.title}**: ${error.message}`);
        }
        playNext(guildId);
    }
}

// Play previous song from history
async function playPrevious(guildId) {
    const state = guildPlayers.get(guildId);
    if (!state) return false;

    if (state.history.length === 0) return false;

    cleanupTrackProcesses(state.currentTrack);

    // Put current track back to front of queue
    if (state.currentTrack) {
        state.queue.unshift({ ...state.currentTrack, resource: undefined });
    }

    // Pop last track from history and put it at front of queue
    const prevTrack = state.history.pop();
    if (!prevTrack) return false;

    state.queue.unshift({ ...prevTrack, resource: undefined });

    state.isTransitioning = true;
    state.status = 'idle';

    // Stop current playback and start the previous song immediately.
    state.audioPlayer.stop(true);
    await playNext(guildId);
    return true;
}

// Stop music playback and clear queue (keep connection)
function stopMusic(guildId) {
    const state = guildPlayers.get(guildId);
    if (!state) return;

    cleanupTrackProcesses(state.currentTrack);
    state.queue = [];
    state.currentTrack = null;
    state.status = 'idle';

    if (state.audioPlayer) {
        state.audioPlayer.stop(true);
    }
}

// Stop player and leave voice channel
function stopPlayer(guildId) {
    // Retrieve connection from registry and destroy it
    const connection = getVoiceConnection(guildId);
    if (connection) {
        connection.destroy();
        console.log(`Successfully destroyed voice connection in guild ${guildId}`);
    } else {
        console.log(`No voice connection found in registry to destroy for guild ${guildId}`);
    }

    const state = guildPlayers.get(guildId);
    if (!state) return;

    cleanupTrackProcesses(state.currentTrack);
    state.queue = [];
    state.currentTrack = null;
    state.status = 'idle';

    if (state.audioPlayer) {
        state.audioPlayer.stop(true);
    }

    state.connection = null;

    if (state.disconnectTimeout) {
        clearTimeout(state.disconnectTimeout);
        state.disconnectTimeout = null;
    }

    guildPlayers.delete(guildId);
}

// Resolve query metadata
async function resolveQuery(query, username) {
    // Spotify URLs
    if (play.sp_validate(query) !== 'search' && play.sp_validate(query) !== false) {
        try {
            if (play.is_expired()) {
                await play.refreshToken();
            }
            const data = await play.spotify(query);
            
            if (data.type === 'track') {
                return [{
                    title: data.name,
                    artist: data.artists.map(a => a.name).join(', '),
                    url: query,
                    duration: data.durationInSec,
                    thumbnail: data.thumbnail?.url || '',
                    requestedBy: username,
                    source: 'spotify'
                }];
            } else {
                // Album / Playlist
                const tracks = await data.all_tracks();
                return tracks.map(track => ({
                    title: track.name,
                    artist: track.artists.map(a => a.name).join(', '),
                    url: track.url,
                    duration: track.durationInSec,
                    thumbnail: track.thumbnail?.url || '',
                    requestedBy: username,
                    source: 'spotify'
                }));
            }
        } catch (err) {
            console.error('Spotify validation error:', err.message);
            // Fallback: search query on YouTube
        }
    }

    // YouTube Playlist
    if (query.includes('youtube.com/playlist') || query.includes('list=')) {
        try {
            const playlist = await play.playlist_info(query, { incomplete: true });
            const videos = await playlist.all_videos();
            return videos.map(video => ({
                title: video.title,
                artist: video.channel?.name || 'Artis YouTube',
                url: video.url,
                duration: video.durationInSec,
                thumbnail: video.thumbnails[0]?.url || '',
                requestedBy: username,
                source: 'youtube'
            }));
        } catch (err) {
            console.error('YouTube playlist error:', err.message);
        }
    }

    // YouTube Video / Short
    if (play.yt_validate(query) === 'video') {
        try {
            const videoInfo = await play.video_basic_info(query);
            const video = videoInfo.video_details;
            return [{
                title: video.title,
                artist: video.channel?.name || 'Artis YouTube',
                url: video.url,
                duration: video.durationInSec,
                thumbnail: video.thumbnails[0]?.url || '',
                requestedBy: username,
                source: 'youtube'
            }];
        } catch (err) {
            console.error('YouTube video error:', err.message);
        }
    }

// Custom Spotify Search function using Client Credentials Flow
async function searchSpotifyTrack(query) {
    const spotifyId = process.env.SPOTIFY_CLIENT_ID;
    const spotifySecret = process.env.SPOTIFY_CLIENT_SECRET;
    
    if (!spotifyId || !spotifySecret) {
        throw new Error("Spotify credentials are not configured in your .env file.");
    }
    
    const creds = Buffer.from(`${spotifyId}:${spotifySecret}`).toString('base64');
    const tokenResponse = await fetch('https://accounts.spotify.com/api/token', {
        method: 'POST',
        headers: {
            'Authorization': `Basic ${creds}`,
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'grant_type=client_credentials'
    });
    
    if (!tokenResponse.ok) {
        throw new Error(`Spotify Auth HTTP ${tokenResponse.status} - ${tokenResponse.statusText}`);
    }
    
    const tokenData = await tokenResponse.json();
    const accessToken = tokenData.access_token;
    
    const searchUrl = `https://api.spotify.com/v1/search?q=${encodeURIComponent(query)}&type=track&limit=1`;
    const searchResponse = await fetch(searchUrl, {
        headers: {
            'Authorization': `Bearer ${accessToken}`
        }
    });
    
    if (!searchResponse.ok) {
        if (searchResponse.status === 403) {
            throw new Error("403 Forbidden. Mohon aktifkan 'Web API' di Spotify Developer Dashboard Anda (App Settings -> APIs Used).");
        }
        throw new Error(`Spotify Search HTTP ${searchResponse.status} - ${searchResponse.statusText}`);
    }
    
    const searchData = await searchResponse.json();
    const track = searchData.tracks?.items?.[0];
    if (!track) {
        return null;
    }
    
    return {
        title: track.name,
        artist: track.artists.map(a => a.name).join(', '),
        url: track.external_urls.spotify,
        duration: Math.floor(track.duration_ms / 1000),
        thumbnail: track.album?.images?.[0]?.url || '',
        source: 'spotify'
    };
}

// Default: Search Text Query on Spotify first, fallback to YouTube if fails (e.g. due to Spotify API limits)
    try {
        const spotifyTrack = await searchSpotifyTrack(query);
        if (spotifyTrack) {
            console.log(`Spotify search found track: "${spotifyTrack.title}" by ${spotifyTrack.artist}`);
            spotifyTrack.requestedBy = username;
            return [spotifyTrack];
        }
    } catch (err) {
        console.warn(`Spotify search failed (${err.message}). Falling back to YouTube search...`);
    }

    console.log(`Falling back to YouTube search for query: "${query}"`);
    try {
        const searchResults = await play.search(query, { limit: 1 });
        if (searchResults.length > 0) {
            const video = searchResults[0];
            return [{
                title: video.title,
                artist: video.channel?.name || 'Artis YouTube',
                url: video.url,
                duration: video.durationInSec,
                thumbnail: video.thumbnails[0]?.url || '',
                requestedBy: username,
                source: 'youtube'
            }];
        }
    } catch (err) {
        console.error('YouTube search fallback error:', err.message);
    }

    throw new Error('Lagu tidak ditemukan di Spotify maupun YouTube.');
}

// Define Discord Slash Commands List
const commandsList = [
    new SlashCommandBuilder()
        .setName('play')
        .setDescription('Memutar musik dari Spotify atau YouTube')
        .addStringOption(option => 
            option.setName('query')
                .setDescription('Judul lagu atau tautan Spotify/YouTube')
                .setRequired(true)),
    new SlashCommandBuilder().setName('skip').setDescription('⏭️ [Owner] Melewati lagu yang sedang diputar'),
    new SlashCommandBuilder().setName('previous').setDescription('⏮️ [Owner] Kembali ke lagu sebelumnya'),
    new SlashCommandBuilder().setName('pause').setDescription('Menjeda lagu yang sedang diputar'),
    new SlashCommandBuilder().setName('resume').setDescription('Melanjutkan pemutaran lagu'),
    new SlashCommandBuilder().setName('stop').setDescription('Menghentikan pemutaran musik dan mengosongkan antrean'),
    new SlashCommandBuilder().setName('quit-bot').setDescription('Mengeluarkan bot dari voice channel'),
    new SlashCommandBuilder()
        .setName('queue')
        .setDescription('Lihat atau hapus antrean lagu')
        .addSubcommand(subcommand =>
            subcommand
                .setName('list')
                .setDescription('Menampilkan antrean lagu saat ini')
        )
        .addSubcommand(subcommand =>
            subcommand
                .setName('delete')
                .setDescription('Menghapus lagu dari antrean')
                .addIntegerOption(option =>
                    option
                        .setName('nomor')
                        .setDescription('Nomor lagu di antrean (mulai dari 1)')
                        .setRequired(true)
                )
        )
].map(command => command.toJSON());

// Discord Client Ready
client.once('ready', async () => {
    console.log(`Logged in as ${client.user.tag}!`);

    const rest = new REST({ version: '10' }).setToken(TOKEN);

    try {
        console.log('Registering slash commands per-guild (instant)...');

        // Clear old global commands to prevent duplication
        await rest.put(Routes.applicationCommands(CLIENT_ID), { body: [] });
        console.log('Cleared global commands.');

        // Register per-guild for instant propagation (no 1-hour wait)
        const guilds = client.guilds.cache.map(g => g.id);
        for (const guildId of guilds) {
            try {
                await rest.put(Routes.applicationGuildCommands(CLIENT_ID, guildId), { body: commandsList });
                console.log(`✅ Commands registered for guild: ${guildId}`);
            } catch (guildError) {
                console.error(`Failed to register commands for guild ${guildId}:`, guildError.message);
            }
        }
    } catch (error) {
        console.error('Failed to register slash commands:', error.message);
    }
});

// Register commands instantly when the bot joins a new guild/server
client.on('guildCreate', async guild => {
    console.log(`Bot joined a new guild: ${guild.name} (${guild.id})`);
    const rest = new REST({ version: '10' }).setToken(TOKEN);
    try {
        await rest.put(Routes.applicationGuildCommands(CLIENT_ID, guild.id), { body: commandsList });
        console.log(`✅ Commands registered successfully for new guild: ${guild.name} (${guild.id})`);
    } catch (error) {
        console.error(`Failed to register commands for new guild ${guild.name}:`, error.message);
    }
});

// Slash Commands Interactions Handler
client.on('interactionCreate', async interaction => {
    if (!interaction.isChatInputCommand()) return;

    const { commandName, guildId, member, channel } = interaction;
    if (!guildId) return;

    const voiceChannel = member?.voice?.channel;
    if (!voiceChannel) {
        return interaction.reply({ content: '❌ Kamu harus bergabung ke voice channel terlebih dahulu!', flags: MessageFlags.Ephemeral });
    }

    const state = getOrCreatePlayer(guildId);
    state.textChannel = channel;

    if (commandName === 'play') {
        await interaction.deferReply();
        const query = interaction.options.getString('query');

        try {
            // Join Voice Connection if not joined
            if (!state.connection) {
                state.connection = joinVoiceChannel({
                    channelId: voiceChannel.id,
                    guildId: guildId,
                    adapterCreator: voiceChannel.guild.voiceAdapterCreator,
                    selfMute: false,
                    selfDeaf: true,
                    debug: true
                });
                
                state.connection.on('stateChange', (oldState, newState) => {
                    console.log(`Voice connection state transition from ${oldState.status} to ${newState.status} in guild ${guildId}`);
                });
                
                state.connection.on('error', error => {
                    console.error(`Voice Connection error in guild ${guildId}:`, error.message);
                });

                state.connection.subscribe(state.audioPlayer);
            }

            const tracks = await resolveQuery(query, interaction.user.username);
            state.queue.push(...tracks);

            if (state.status === 'idle') {
                playNext(guildId);
                await interaction.editReply(`🎶 Memulai pemutaran: **${tracks[0].title}**`);
            } else {
                await interaction.editReply(`✅ Menambahkan **${tracks.length} lagu** ke antrean.`);
            }
        } catch (error) {
            console.error(error);
            await interaction.editReply(`❌ Gagal: ${error.message}`);
        }
    }

    if (commandName === 'skip') {
        // Owner only
        if (interaction.guild.ownerId !== interaction.user.id) {
            return interaction.reply({ content: '❌ Hanya **owner server** yang bisa menggunakan perintah ini!', flags: MessageFlags.Ephemeral });
        }
        if (state.status === 'idle') {
            return interaction.reply({ content: '❌ Tidak ada lagu yang sedang diputar.', flags: MessageFlags.Ephemeral });
        }
        state.isTransitioning = true;
        await playNext(guildId);
        return interaction.reply('⏭️ Lagu dilewati.');
    }

    if (commandName === 'previous') {
        // Owner only
        if (interaction.guild.ownerId !== interaction.user.id) {
            return interaction.reply({ content: '❌ Hanya **owner server** yang bisa menggunakan perintah ini!', flags: MessageFlags.Ephemeral });
        }
        const ok = await playPrevious(guildId);
        if (!ok) {
            return interaction.reply({ content: '❌ Tidak ada riwayat lagu sebelumnya.', flags: MessageFlags.Ephemeral });
        }
        return interaction.reply('⏮️ Kembali ke lagu sebelumnya.');
    }

    if (commandName === 'pause') {
        if (state.status !== 'playing') {
            return interaction.reply({ content: '❌ Bot tidak sedang memutar lagu.', flags: MessageFlags.Ephemeral });
        }
        state.audioPlayer.pause();
        state.status = 'paused';
        return interaction.reply('⏸️ Musik dijeda.');
    }

    if (commandName === 'resume') {
        if (state.status !== 'paused') {
            return interaction.reply({ content: '❌ Musik tidak sedang dijeda.', flags: MessageFlags.Ephemeral });
        }
        state.audioPlayer.unpause();
        state.status = 'playing';
        return interaction.reply('▶️ Melanjutkan pemutaran musik.');
    }

    if (commandName === 'stop') {
        stopMusic(guildId);
        return interaction.reply('⏹️ Pemutaran musik dihentikan dan antrean dikosongkan.');
    }

    if (commandName === 'quit-bot') {
        stopPlayer(guildId);
        return interaction.reply('🚪 Bot telah keluar dari voice channel.');
    }

    if (commandName === 'queue') {
        const subcommand = interaction.options.getSubcommand();

        if (subcommand === 'delete') {
            const nomor = interaction.options.getInteger('nomor');
            const index = nomor - 1;

            if (!Number.isInteger(index) || index < 0 || index >= state.queue.length) {
                return interaction.reply({ content: '❌ Nomor antrean tidak valid.', flags: MessageFlags.Ephemeral });
            }

            const removedTrack = state.queue.splice(index, 1)[0];
            return interaction.reply(`🗑️ Lagu "${removedTrack.title}" berhasil dihapus dari antrean.`);
        }

        if (state.queue.length === 0 && !state.currentTrack) {
            return interaction.reply('Antrean kosong.');
        }

        let qMsg = `🎶 **Sedang diputar:** ${state.currentTrack?.title || 'Tidak ada'}\n\n**Antrean:**\n`;
        state.queue.slice(0, 10).forEach((t, i) => {
            qMsg += `${i + 1}. **${t.title}** - ${t.artist}\n`;
        });
        if (state.queue.length > 10) {
            qMsg += `...dan ${state.queue.length - 10} lagu lainnya.`;
        }
        return interaction.reply(qMsg);
    }
});

// Express App Configuration
const app = express();
app.use(express.json());

// Get guilds the bot is currently in
app.get('/api/guilds', (req, res) => {
    const ids = client.guilds.cache.map(g => g.id);
    res.json(ids);
});

// Get status of a guild player
app.get('/api/guilds/:guildId/status', (req, res) => {
    const { guildId } = req.params;
    const state = guildPlayers.get(guildId);

    if (!state || !state.connection) {
        return res.json({ connected: false });
    }

    // Calculate current track progress
    let currentTime = 0;
    if (state.currentTrack && state.currentTrack.startedAt) {
        if (state.status === 'playing') {
            const resource = state.currentTrack.resource;
            currentTime = resource ? Math.floor(resource.playbackDuration / 1000) : 0;
        } else if (state.status === 'paused') {
            // Static playback duration calculation if paused
            currentTime = state.currentTrack.pausedTime || 0;
        }
    }

    res.json({
        connected: true,
        playbackState: state.status,
        volume: Math.round(state.volume * 100),
        currentTrack: state.currentTrack ? {
            title: state.currentTrack.title,
            artist: state.currentTrack.artist,
            url: state.currentTrack.url,
            duration: state.currentTrack.duration,
            thumbnail: state.currentTrack.thumbnail,
            requestedBy: state.currentTrack.requestedBy,
            currentTime: currentTime
        } : null,
        queue: state.queue.map(q => ({
            title: q.title,
            artist: q.artist,
            duration: q.duration,
            thumbnail: q.thumbnail
        }))
    });
});

// Play/Queue a track via REST API
app.post('/api/guilds/:guildId/play', async (req, res) => {
    const { guildId } = req.params;
    const { query, userId, username } = req.body;

    try {
        const guild = client.guilds.cache.get(guildId);
        if (!guild) {
            return res.status(404).json({ success: false, error: 'Guild tidak ditemukan.' });
        }

        // Fetch member to check voice status
        const member = await guild.members.fetch(userId).catch(() => null);
        const voiceChannel = member?.voice?.channel;

        if (!voiceChannel) {
            return res.json({ success: false, error: 'Anda harus bergabung ke voice channel di server Discord terlebih dahulu!' });
        }

        const state = getOrCreatePlayer(guildId);

        // Join Voice Channel
        if (!state.connection) {
            state.connection = joinVoiceChannel({
                channelId: voiceChannel.id,
                guildId: guildId,
                adapterCreator: guild.voiceAdapterCreator,
                selfMute: false,
                selfDeaf: true,
                debug: true
            });
            
            state.connection.on('stateChange', (oldState, newState) => {
                console.log(`Voice connection state transition from ${oldState.status} to ${newState.status} in guild ${guildId}`);
            });
            
            state.connection.on('error', error => {
                console.error(`Voice Connection error in guild ${guildId}:`, error.message);
            });
            
            // Re-subscribe player
            state.connection.subscribe(state.audioPlayer);
        }

        const tracks = await resolveQuery(query, username || 'Dashboard User');
        state.queue.push(...tracks);

        if (state.status === 'idle') {
            playNext(guildId);
            return res.json({ success: true, message: `🎶 Memulai pemutaran: ${tracks[0].title}` });
        } else {
            return res.json({ success: true, message: `✅ Berhasil menambahkan ${tracks.length} lagu ke antrean.` });
        }

    } catch (error) {
        console.error('Play API Error:', error.message);
        res.json({ success: false, error: error.message });
    }
});

// Pause playback
app.post('/api/guilds/:guildId/pause', (req, res) => {
    const { guildId } = req.params;
    const state = guildPlayers.get(guildId);

    if (!state || state.status !== 'playing') {
        return res.json({ success: false, error: 'Musik tidak sedang diputar.' });
    }

    state.audioPlayer.pause();
    
    // Track paused duration
    if (state.currentTrack) {
        const resource = state.currentTrack.resource;
        state.currentTrack.pausedTime = resource ? Math.floor(resource.playbackDuration / 1000) : 0;
    }
    
    state.status = 'paused';
    res.json({ success: true, message: '⏸️ Musik berhasil dijeda.' });
});

// Resume playback
app.post('/api/guilds/:guildId/resume', (req, res) => {
    const { guildId } = req.params;
    const state = guildPlayers.get(guildId);

    if (!state || state.status !== 'paused') {
        return res.json({ success: false, error: 'Musik tidak sedang dijeda.' });
    }

    state.audioPlayer.unpause();
    state.status = 'playing';
    res.json({ success: true, message: '▶️ Melanjutkan pemutaran musik.' });
});

// Helper: check if userId is guild owner
async function isGuildOwner(guildId, userId) {
    try {
        const guild = await client.guilds.fetch(guildId);
        return guild.ownerId === userId;
    } catch {
        return false;
    }
}

// Skip current track (owner only)
app.post('/api/guilds/:guildId/skip', async (req, res) => {
    const { guildId } = req.params;
    const { userId } = req.body;
    const state = guildPlayers.get(guildId);

    if (!state || state.status === 'idle') {
        return res.json({ success: false, error: 'Tidak ada lagu yang sedang diputar.' });
    }

    if (userId && !(await isGuildOwner(guildId, userId))) {
        return res.json({ success: false, error: 'Hanya owner server yang bisa skip/previous.' });
    }

    state.isTransitioning = true;
    await playNext(guildId);
    res.json({ success: true, message: '⏭️ Lagu berhasil dilewati.' });
});

// Previous track (owner only)
app.post('/api/guilds/:guildId/previous', async (req, res) => {
    const { guildId } = req.params;
    const { userId } = req.body;
    const state = guildPlayers.get(guildId);

    if (!state) {
        return res.json({ success: false, error: 'Player tidak ditemukan.' });
    }

    if (userId && !(await isGuildOwner(guildId, userId))) {
        return res.json({ success: false, error: 'Hanya owner server yang bisa skip/previous.' });
    }

    const ok = await playPrevious(guildId);
    if (!ok) {
        return res.json({ success: false, error: 'Tidak ada riwayat lagu sebelumnya.' });
    }
    res.json({ success: true, message: '⏮️ Kembali ke lagu sebelumnya.' });
});

// Stop music (keep channel connection)
app.post('/api/guilds/:guildId/stop', (req, res) => {
    const { guildId } = req.params;
    
    stopMusic(guildId);
    res.json({ success: true, message: '⏹️ Pemutaran musik dihentikan dan antrean dikosongkan.' });
});

// Quit voice channel
app.post('/api/guilds/:guildId/quit', (req, res) => {
    const { guildId } = req.params;
    
    stopPlayer(guildId);
    res.json({ success: true, message: '🚪 Bot telah keluar dari voice channel.' });
});

// Set volume
app.post('/api/guilds/:guildId/volume', (req, res) => {
    const { guildId } = req.params;
    const { volume } = req.body; // 0 - 100
    const state = guildPlayers.get(guildId);

    if (!state) {
        return res.json({ success: false, error: 'Player tidak ditemukan.' });
    }

    const vol = parseFloat(volume) / 100;
    state.volume = vol;
    
    // Apply to playing track resource
    if (state.currentTrack && state.currentTrack.resource) {
        state.currentTrack.resource.volume?.setVolume(vol);
    }

    res.json({ success: true, message: `🔊 Volume disesuaikan ke ${volume}%` });
});

// Remove one queued track
app.post('/api/guilds/:guildId/remove', async (req, res) => {
    const { guildId } = req.params;
    const { userId } = req.body;
    const state = guildPlayers.get(guildId);

    if (!state) {
        return res.json({ success: false, error: 'Player tidak ditemukan.' });
    }

    if (userId && !(await isGuildOwner(guildId, userId))) {
        return res.json({ success: false, error: 'Hanya owner server yang bisa menghapus lagu dari antrean.' });
    }

    const index = Number(req.body.index ?? -1);
    if (!Number.isInteger(index) || index < 0 || index >= state.queue.length) {
        return res.json({ success: false, error: 'Indeks lagu tidak valid.' });
    }

    const removedTrack = state.queue.splice(index, 1)[0];

    res.json({
        success: true,
        message: `🗑️ Lagu "${removedTrack.title}" dihapus dari antrean.`
    });
});

// Clear queue
app.post('/api/guilds/:guildId/clear', (req, res) => {
    const { guildId } = req.params;
    const state = guildPlayers.get(guildId);

    if (!state) {
        return res.json({ success: false, error: 'Player tidak ditemukan.' });
    }

    state.queue = [];
    res.json({ success: true, message: '🗑️ Daftar antrean dibersihkan.' });
});

// Start Express server and log in bot
app.listen(PORT, () => {
    console.log(`REST API Control Server listening on http://localhost:${PORT}`);
});

client.login(TOKEN);
