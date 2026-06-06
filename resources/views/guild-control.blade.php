@extends('layouts.app')

@section('title', $guildDetails['name'] . ' - Control Panel')

@section('content')
<div class="space-y-6">
    <!-- Server Header / Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-900/85">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-[#0a0814]/80 border border-slate-900 hover:border-violet-500/30 hover:bg-violet-950/15 text-slate-400 hover:text-violet-350 transition-colors" title="Kembali ke Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            
            <div class="flex items-center gap-3">
                @if($guildDetails['icon'])
                    <img src="https://cdn.discordapp.com/icons/{{ $guildDetails['id'] }}/{{ $guildDetails['icon'] }}.png" alt="{{ $guildDetails['name'] }}" class="w-10 h-10 rounded-xl border border-slate-900">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-950 border border-slate-800 flex items-center justify-center font-bold text-slate-300">
                        {{ Str::upper(Str::substr($guildDetails['name'], 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ $guildDetails['name'] }}</h1>
                    <p class="text-xs text-slate-500">ID: {{ $guildDetails['id'] }}</p>
                </div>
            </div>
        </div>

        <a href="{{ $botInviteUrl }}" target="_blank" class="px-4 py-2 rounded-xl bg-[#0a0814]/80 border border-slate-900 hover:border-violet-500/30 hover:bg-violet-950/10 text-sm font-semibold text-slate-300 hover:text-white transition-all duration-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#5865F2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Re-Invite Bot
        </a>
    </div>

    <!-- Offline/Disconnected Banner -->
    <div id="bot-offline-banner" class="hidden p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex gap-3.5 items-start shadow-lg shadow-amber-500/2">
        <svg class="w-6 h-6 text-amber-550 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h3 class="font-bold text-white text-sm">Bot tidak aktif di voice channel</h3>
            <p class="text-slate-400 text-xs mt-1">Bot belum bergabung ke voice channel di server ini. Bergabunglah ke salah satu voice channel di Discord dan putar musik untuk memulai.</p>
        </div>
    </div>

    <!-- Main Player Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Player & Controls (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Now Playing Card -->
            <div class="glass-card rounded-3xl p-6 relative overflow-hidden purple-glow">
                
                <!-- Inner Layout -->
                <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
                    <!-- Album Art Cover -->
                    <div class="relative w-44 h-44 shrink-0 rounded-2xl overflow-hidden shadow-2xl bg-[#0a0814] border border-slate-900 flex items-center justify-center group">
                        <img id="track-cover" src="" alt="Album Art" class="hidden w-full h-full object-cover">
                        <div id="track-cover-placeholder" class="w-full h-full bg-gradient-to-tr from-[#0a0814] to-[#120f26] flex items-center justify-center">
                            <!-- Animated Wave Visualizer -->
                            <div id="visualizer-bars" class="flex items-end gap-1.5 h-12">
                                <div class="w-1.5 h-6 bg-violet-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-1.5 h-10 bg-violet-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                                <div class="w-1.5 h-4 bg-violet-500 rounded-full animate-bounce" style="animation-delay: 0.5s"></div>
                                <div class="w-1.5 h-8 bg-violet-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                <div class="w-1.5 h-5 bg-violet-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Track Details -->
                    <div class="flex-1 min-w-0 text-center sm:text-left space-y-3">
                        <span id="playing-badge" class="px-2.5 py-0.5 rounded-md bg-violet-500/10 border border-violet-500/20 text-xs font-bold text-violet-400 inline-block uppercase tracking-wider shadow-sm">
                            Sedang Diputar
                        </span>
                        
                        <div>
                            <h2 id="track-title" class="text-2xl font-extrabold text-white truncate leading-tight">Tidak ada lagu diputar</h2>
                            <p id="track-artist" class="text-slate-400 mt-1 truncate font-medium">Hubungkan bot dan putar musik</p>
                        </div>

                        <div id="track-requester-container" class="hidden text-xs text-slate-500 items-center gap-1.5 justify-center sm:justify-start">
                            <span>Diminta oleh:</span>
                            <span id="track-requester" class="font-medium text-slate-350 bg-violet-950/20 border border-violet-500/10 px-2 py-0.5 rounded-md"></span>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar & Duration -->
                <div class="mt-8 space-y-2 relative z-10">
                    <div class="w-full bg-slate-900 rounded-full h-1.5 relative overflow-hidden">
                        <div id="progress-bar-fill" class="bg-gradient-to-r from-violet-600 via-fuchsia-500 to-indigo-500 h-full w-0 transition-all duration-1000 shadow-sm shadow-violet-500/20"></div>
                    </div>
                    <div class="flex justify-between text-xs font-semibold text-slate-500">
                        <span id="progress-time">0:00</span>
                        <span id="total-time">0:00</span>
                    </div>
                </div>

                <!-- Control Buttons -->
                <div class="mt-8 flex items-center justify-center gap-6 relative z-10">
                    <!-- Clear Button -->
                    <button onclick="controlAction('clear')" class="p-3 rounded-full hover:bg-violet-950/25 border border-transparent hover:border-violet-500/10 text-slate-400 hover:text-violet-300 transition-all duration-300" title="Kosongkan Antrean">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>

                    <!-- Previous Button -->
                    <button onclick="controlAction('previous')" class="p-3 rounded-full bg-violet-950/20 hover:bg-violet-950/40 hover:scale-105 text-violet-300 transition-all duration-300 shadow-md border border-violet-500/20" title="Lagu Sebelumnya">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M11.5 12l8.5 6V6zm-9 6h2V6h-2z"/>
                        </svg>
                    </button>

                    <!-- Play / Pause Button -->
                    <button id="btn-play-pause" onclick="togglePlayPause()" class="w-14 h-14 rounded-full bg-white hover:bg-violet-50 text-[#030206] hover:scale-105 transition-all duration-300 flex items-center justify-center shadow-lg shadow-violet-500/20" title="Putar / Jeda">
                        <!-- Play Icon -->
                        <svg id="icon-play" class="w-6 h-6 fill-current text-[#030206]" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <!-- Pause Icon -->
                        <svg id="icon-pause" class="hidden w-6 h-6 fill-current text-[#030206]" viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>

                    <!-- Skip Button -->
                    <button onclick="controlAction('skip')" class="p-3 rounded-full bg-violet-950/20 hover:bg-violet-950/40 hover:scale-105 text-violet-300 transition-all duration-300 shadow-md border border-violet-500/20" title="Lewati Lagu">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                        </svg>
                    </button>

                    <!-- Stop Button -->
                    <button onclick="controlAction('stop')" class="p-3 rounded-full hover:bg-violet-950/25 border border-transparent hover:border-violet-500/10 text-slate-400 hover:text-violet-300 transition-all duration-300" title="Hentikan Musik">
                        <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24">
                            <path d="M6 6h12v12H6z"/>
                        </svg>
                    </button>

                    <!-- Quit Bot Button -->
                    <button onclick="controlAction('quit')" class="p-3 rounded-full hover:bg-rose-950/35 border border-transparent hover:border-rose-500/25 text-rose-450 hover:text-rose-400 transition-all duration-300" title="Keluarkan Bot dari Voice Channel">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Volume & Settings Panel -->
            <div class="glass-card rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-6">
                <!-- Volume Control -->
                <div class="flex items-center gap-4 w-full sm:max-w-xs">
                    <svg class="w-5.5 h-5.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    <input id="volume-slider" type="range" min="0" max="100" value="50" onchange="changeVolume(this.value)" class="w-full h-1 bg-slate-900 rounded-lg appearance-none cursor-pointer accent-violet-500">
                    <span id="volume-display" class="text-sm font-semibold text-slate-350 w-8 text-right">50%</span>
                </div>
                
                <!-- Status Details -->
                <div class="text-xs text-slate-400 text-center sm:text-right">
                    <span>Status Koneksi: </span>
                    <span id="connection-status" class="font-bold text-slate-500">Memeriksa...</span>
                </div>
            </div>
        </div>

        <!-- Right: Search, Add & Queue (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Search & Play Card -->
            <div class="glass-card rounded-2xl p-5 space-y-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                    </svg>
                    Putar Musik
                </h2>
                
                <form id="play-form" onsubmit="handlePlaySubmit(event)" class="space-y-3">
                    <div class="relative">
                        <input id="search-input" type="text" placeholder="Masukkan judul lagu / tautan Spotify..." class="w-full bg-[#0a0814] border border-slate-900 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-violet-500 transition-colors shadow-inner" required>
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 hover:scale-[1.01] transition-all duration-300 font-bold text-white text-sm flex items-center justify-center gap-2 shadow-lg shadow-violet-600/15">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambahkan ke Antrean
                    </button>
                </form>
            </div>

            <!-- Queue Card -->
            <div class="glass-card rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-900 pb-3">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Antrean Lagu
                    </h2>
                    <span id="queue-length-badge" class="px-2.5 py-0.5 rounded-full bg-violet-950/20 border border-violet-500/10 text-xs font-semibold text-violet-300">0 Lagu</span>
                </div>
                
                <!-- Queue List -->
                <div id="queue-list" class="space-y-3 max-h-80 overflow-y-auto pr-1">
                    <!-- Queue items will be rendered dynamically -->
                    <div class="py-8 text-center text-slate-500 text-sm">
                        Antrean kosong.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const guildId = "{{ $guildDetails['id'] }}";
    let isPlaying = false;
    let pollInterval = null;

    // Toast feedback notification builder
    function showNotification(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 px-4 py-3 rounded-xl shadow-2xl z-50 transform translate-y-10 opacity-0 transition-all duration-300 flex items-center gap-2.5 text-sm font-semibold border ${
            type === 'success' 
            ? 'bg-violet-950/95 text-violet-350 border-violet-500/30' 
            : 'bg-rose-950/95 text-rose-300 border-rose-500/30'
        }`;
        
        toast.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full ${type === 'success' ? 'bg-violet-400' : 'bg-rose-450'}"></span>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
        }, 10);
        
        setTimeout(() => {
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Submit actions (Play/Pause, Skip, Stop, Volume)
    async function controlAction(action, data = {}) {
        try {
            const response = await fetch(`/dashboard/guild/${guildId}/control`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ action, ...data })
            });
            
            if (!response.ok) {
                showNotification(`Gagal: Server merespon dengan status ${response.status}`, 'error');
                return;
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                showNotification('Sesi Anda telah kedaluwarsa. Silakan muat ulang halaman.', 'error');
                return;
            }
            
            const result = await response.json();
            
            if (result.success) {
                if (result.message) {
                    showNotification(result.message);
                }
                fetchStatus();
            } else {
                showNotification(result.error || 'Terjadi kesalahan.', 'error');
            }
        } catch (error) {
            console.error(error);
            showNotification('Tidak dapat terhubung ke bot.', 'error');
        }
    }

    function togglePlayPause() {
        controlAction(isPlaying ? 'pause' : 'resume');
    }

    function changeVolume(value) {
        document.getElementById('volume-display').textContent = `${value}%`;
        controlAction('volume', { volume: parseInt(value) });
    }

    async function handlePlaySubmit(event) {
        event.preventDefault();
        const input = document.getElementById('search-input');
        const query = input.value.trim();
        if (!query) return;

        showNotification('Mencari lagu...');
        input.value = '';
        
        await controlAction('play', { query });
    }

    // Convert seconds to MM:SS format
    function formatTime(seconds) {
        if (isNaN(seconds) || seconds === null) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    // Update the visual visualizer bar animation
    function updateVisualizer(playing) {
        const visualizer = document.getElementById('visualizer-bars');
        const bars = visualizer.querySelectorAll('div');
        bars.forEach(bar => {
            if (playing) {
                bar.classList.add('animate-bounce');
            } else {
                bar.classList.remove('animate-bounce');
            }
        });
    }

    // Main fetch loop to get latest state from bot
    async function fetchStatus() {
        try {
            const response = await fetch(`/dashboard/guild/${guildId}/status`);
            
            if (!response.ok) {
                console.warn(`Fetch status failed with HTTP ${response.status}`);
                return;
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.warn('Received non-JSON response from status endpoint, likely session expired.');
                return;
            }
            
            const status = await response.json();
            
            const banner = document.getElementById('bot-offline-banner');
            const connectionStatusEl = document.getElementById('connection-status');
            
            if (!status.connected) {
                // Bot offline
                banner.classList.remove('hidden');
                connectionStatusEl.textContent = 'Terputus';
                connectionStatusEl.className = 'font-bold text-rose-500';
                
                // Reset states
                isPlaying = false;
                document.getElementById('track-title').textContent = 'Tidak ada lagu diputar';
                document.getElementById('track-artist').textContent = 'Hubungkan bot dan putar musik';
                document.getElementById('progress-time').textContent = '0:00';
                document.getElementById('total-time').textContent = '0:00';
                document.getElementById('progress-bar-fill').style.width = '0%';
                
                document.getElementById('icon-play').classList.remove('hidden');
                document.getElementById('icon-pause').classList.add('hidden');
                document.getElementById('track-cover').classList.add('hidden');
                document.getElementById('track-cover-placeholder').classList.remove('hidden');
                
                updateVisualizer(false);
                document.getElementById('queue-list').innerHTML = `
                    <div class="py-8 text-center text-slate-500 text-sm">
                        Bot tidak terhubung ke voice channel.
                    </div>
                `;
                document.getElementById('queue-length-badge').textContent = '0 Lagu';
                return;
            }

            // Bot online
            banner.classList.add('hidden');
            connectionStatusEl.textContent = 'Terhubung';
            connectionStatusEl.className = 'font-bold text-violet-400';

            // Set play state
            isPlaying = status.playbackState === 'playing';
            
            if (isPlaying) {
                document.getElementById('icon-play').classList.add('hidden');
                document.getElementById('icon-pause').classList.remove('hidden');
                document.getElementById('playing-badge').textContent = 'Sedang Diputar';
                document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-violet-500/10 border border-violet-500/20 text-xs font-bold text-violet-400 inline-block uppercase tracking-wider';
                updateVisualizer(true);
            } else if (status.playbackState === 'paused') {
                document.getElementById('icon-play').classList.remove('hidden');
                document.getElementById('icon-pause').classList.add('hidden');
                document.getElementById('playing-badge').textContent = 'Jeda';
                document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/20 text-xs font-bold text-amber-500 inline-block uppercase tracking-wider';
                updateVisualizer(false);
            } else {
                // idle
                document.getElementById('icon-play').classList.remove('hidden');
                document.getElementById('icon-pause').classList.add('hidden');
                document.getElementById('playing-badge').textContent = 'Berhenti';
                document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-slate-900 border border-slate-800 text-xs font-bold text-slate-500 inline-block uppercase tracking-wider';
                updateVisualizer(false);
            }

            // Set current song
            if (status.currentTrack) {
                document.getElementById('track-title').textContent = status.currentTrack.title;
                document.getElementById('track-artist').textContent = status.currentTrack.artist || 'Artis Tidak Diketahui';
                
                // Requester
                if (status.currentTrack.requestedBy) {
                    document.getElementById('track-requester-container').classList.remove('hidden');
                    document.getElementById('track-requester-container').classList.add('flex');
                    document.getElementById('track-requester').textContent = status.currentTrack.requestedBy;
                } else {
                    document.getElementById('track-requester-container').classList.add('hidden');
                    document.getElementById('track-requester-container').classList.remove('flex');
                }

                // Album cover
                const coverUrl = status.currentTrack.thumbnail;
                const coverImg = document.getElementById('track-cover');
                const placeholder = document.getElementById('track-cover-placeholder');
                
                if (coverUrl) {
                    coverImg.src = coverUrl;
                    coverImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                } else {
                    coverImg.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                }

                // Progress
                const cur = status.currentTrack.currentTime || 0;
                const dur = status.currentTrack.duration || 0;
                document.getElementById('progress-time').textContent = formatTime(cur);
                document.getElementById('total-time').textContent = formatTime(dur);
                
                const percent = dur > 0 ? (cur / dur) * 100 : 0;
                document.getElementById('progress-bar-fill').style.width = `${percent}%`;
            } else {
                document.getElementById('track-title').textContent = 'Tidak ada lagu diputar';
                document.getElementById('track-artist').textContent = 'Hubungkan bot dan putar musik';
                document.getElementById('track-requester-container').classList.add('hidden');
                
                document.getElementById('track-cover').classList.add('hidden');
                document.getElementById('track-cover-placeholder').classList.remove('hidden');
                
                document.getElementById('progress-time').textContent = '0:00';
                document.getElementById('total-time').textContent = '0:00';
                document.getElementById('progress-bar-fill').style.width = '0%';
            }

            // Set volume
            if (status.volume !== undefined) {
                document.getElementById('volume-slider').value = status.volume;
                document.getElementById('volume-display').textContent = `${status.volume}%`;
            }

            // Set queue
            const queueList = document.getElementById('queue-list');
            document.getElementById('queue-length-badge').textContent = `${status.queue.length} Lagu`;
            
            if (status.queue.length > 0) {
                queueList.innerHTML = status.queue.map((track, index) => `
                    <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-950/45 border border-slate-900 hover:border-violet-500/20 hover:bg-slate-900/60 transition-colors duration-300">
                        <div class="text-xs font-semibold text-slate-600 w-5 text-center">${index + 1}</div>
                        ${track.thumbnail 
                            ? `<img src="${track.thumbnail}" alt="" class="w-10 h-10 rounded-lg object-cover">` 
                            : `<div class="w-10 h-10 rounded-lg bg-[#0a0814] border border-slate-900 flex items-center justify-center text-slate-500">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                 </svg>
                               </div>`
                        }
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-white truncate">${track.title}</div>
                            <div class="text-xs text-slate-550 truncate">${track.artist || 'Artis Tidak Diketahui'}</div>
                        </div>
                        <div class="text-xs font-semibold text-slate-550 pr-1">${formatTime(track.duration)}</div>
                        <button type="button" onclick="controlAction('remove', { index: ${index} })" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors" title="Hapus lagu ini dari antrean">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                `).join('');
            } else {
                queueList.innerHTML = `
                    <div class="py-8 text-center text-slate-600 text-sm">
                        Antrean kosong.
                    </div>
                `;
            }

        } catch (error) {
            console.error('Error fetching player status:', error);
        }
    }

    // Run polling
    document.addEventListener('DOMContentLoaded', () => {
        fetchStatus();
        pollInterval = setInterval(fetchStatus, 3000);
    });

    // Cleanup interval on unload
    window.addEventListener('beforeunload', () => {
        if (pollInterval) clearInterval(pollInterval);
    });
</script>
@endsection
