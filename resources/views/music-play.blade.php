@extends('layouts.app')

@section('title', 'Standalone Music Player')

@section('content')
<div class="space-y-6">
    <!-- Header / Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-900/85">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-violet-600/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Standalone Music Player</h1>
                <p class="text-xs text-slate-500">Mendengarkan & Kelola Antrean secara Lokal (Tanpa Discord)</p>
            </div>
        </div>
        
        <div class="px-4 py-2 rounded-xl bg-[#0a0814]/80 border border-slate-900 text-sm font-semibold text-violet-400 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Mode Lokal / Standalone
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
                                <div class="w-1.5 h-6 bg-violet-500 rounded-full" style="animation-delay: 0.1s"></div>
                                <div class="w-1.5 h-10 bg-violet-500 rounded-full" style="animation-delay: 0.3s"></div>
                                <div class="w-1.5 h-4 bg-violet-500 rounded-full" style="animation-delay: 0.5s"></div>
                                <div class="w-1.5 h-8 bg-violet-500 rounded-full" style="animation-delay: 0.2s"></div>
                                <div class="w-1.5 h-5 bg-violet-500 rounded-full" style="animation-delay: 0.4s"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Track Details -->
                    <div class="flex-1 min-w-0 text-center sm:text-left space-y-3">
                        <span id="playing-badge" class="px-2.5 py-0.5 rounded-md bg-slate-900 border border-slate-800 text-xs font-bold text-slate-500 inline-block uppercase tracking-wider shadow-sm">
                            Berhenti
                        </span>
                        
                        <div>
                            <h2 id="track-title" class="text-2xl font-extrabold text-white truncate leading-tight">Tidak ada lagu diputar</h2>
                            <p id="track-artist" class="text-slate-400 mt-1 truncate font-medium">Cari lagu dan tambahkan ke antrean</p>
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
                        <div id="progress-bar-fill" class="bg-gradient-to-r from-violet-600 via-fuchsia-500 to-indigo-500 h-full w-0 transition-all duration-300 shadow-sm shadow-violet-500/20"></div>
                    </div>
                    <div class="flex justify-between text-xs font-semibold text-slate-500">
                        <span id="progress-time">0:00</span>
                        <span id="total-time">0:00</span>
                    </div>
                </div>

                <!-- Control Buttons -->
                <div class="mt-8 flex items-center justify-center gap-6 relative z-10">
                    <!-- Clear Button -->
                    <button onclick="clearQueue()" class="p-3 rounded-full hover:bg-violet-950/25 border border-transparent hover:border-violet-500/10 text-slate-400 hover:text-violet-300 transition-all duration-300" title="Kosongkan Antrean">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>

                    <!-- Previous Button -->
                    <button onclick="playPrevious()" class="p-3 rounded-full bg-violet-950/20 hover:bg-violet-950/40 hover:scale-105 text-violet-300 transition-all duration-300 shadow-md border border-violet-500/20" title="Lagu Sebelumnya">
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
                    <button onclick="playNext()" class="p-3 rounded-full bg-violet-950/20 hover:bg-violet-950/40 hover:scale-105 text-violet-300 transition-all duration-300 shadow-md border border-violet-500/20" title="Lewati Lagu">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                        </svg>
                    </button>

                    <!-- Stop Button -->
                    <button onclick="stopPlayback()" class="p-3 rounded-full hover:bg-violet-950/25 border border-transparent hover:border-violet-500/10 text-slate-400 hover:text-violet-300 transition-all duration-300" title="Hentikan Musik">
                        <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24">
                            <path d="M6 6h12v12H6z"/>
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
                    <input id="volume-slider" type="range" min="0" max="100" value="50" oninput="changeVolume(this.value)" class="w-full h-1 bg-slate-900 rounded-lg appearance-none cursor-pointer accent-violet-500">
                    <span id="volume-display" class="text-sm font-semibold text-slate-350 w-8 text-right">50%</span>
                </div>
                
                <!-- Status Details -->
                <div class="text-xs text-slate-400 text-center sm:text-right">
                    <span>Sumber Data: </span>
                    <span class="font-bold text-violet-400">Spotify & YouTube API</span>
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
                    Cari & Tambahkan Musik
                </h2>
                
                <form id="play-form" onsubmit="handleSearchSubmit(event)" class="space-y-3">
                    <div class="relative">
                        <input id="search-input" type="text" placeholder="Masukkan judul lagu / tautan Spotify..." class="w-full bg-[#0a0814] border border-slate-900 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-violet-500 transition-colors shadow-inner" required>
                    </div>
                    <button id="search-btn" type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 hover:scale-[1.01] transition-all duration-300 font-bold text-white text-sm flex items-center justify-center gap-2 shadow-lg shadow-violet-600/15">
                        <svg class="w-4.5 h-4.5 animate-spin hidden" id="spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btn-text">Tambahkan ke Antrean</span>
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
                    <div class="py-8 text-center text-slate-500 text-sm">
                        Antrean kosong.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden native audio element -->
<audio id="audio-player" class="hidden"></audio>

<script>
    // State management
    let queue = [];
    let history = [];
    let currentTrack = null;
    let isPlaying = false;
    let playbackTime = 0;
    let volume = 50;

    const audio = document.getElementById('audio-player');

    // IMPORTANT: Clear localStorage on load/refresh so queue is reset
    localStorage.removeItem('music_play_queue');
    localStorage.removeItem('music_play_current_track');
    localStorage.removeItem('music_play_history');
    localStorage.removeItem('music_play_status');

    // Toast feedback notification builder
    function showNotification(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 px-4 py-3 rounded-xl shadow-2xl z-50 transform translate-y-10 opacity-0 transition-all duration-300 flex items-center gap-2.5 text-sm font-semibold border ${
            type === 'success' 
            ? 'bg-violet-950/95 text-violet-350 border-violet-500/30' 
            : type === 'error'
            ? 'bg-rose-950/95 text-rose-300 border-rose-500/30'
            : 'bg-indigo-950/95 text-indigo-350 border-indigo-500/30'
        }`;
        
        toast.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full ${type === 'success' ? 'bg-violet-400' : type === 'error' ? 'bg-rose-450' : 'bg-indigo-400'}"></span>
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

    // Save to localStorage helper
    function saveToStorage() {
        localStorage.setItem('music_play_queue', JSON.stringify(queue));
        localStorage.setItem('music_play_current_track', JSON.stringify(currentTrack));
        localStorage.setItem('music_play_history', JSON.stringify(history));
        localStorage.setItem('music_play_status', JSON.stringify({ isPlaying, playbackTime, volume }));
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

    // Change Volume
    function changeVolume(val) {
        volume = val;
        document.getElementById('volume-display').textContent = `${val}%`;
        audio.volume = val / 100;
        saveToStorage();
    }

    // Handle song search and add
    async function handleSearchSubmit(event) {
        event.preventDefault();
        const input = document.getElementById('search-input');
        const queryStr = input.value.trim();
        if (!queryStr) return;

        const btn = document.getElementById('search-btn');
        const spinner = document.getElementById('spinner');
        const btnText = document.getElementById('btn-text');

        // Loading state UI
        input.disabled = true;
        btn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.textContent = 'Mencari...';

        try {
            const response = await fetch(`/api/music/search?query=${encodeURIComponent(queryStr)}`);
            const result = await response.json();

            if (result.success && result.tracks && result.tracks.length > 0) {
                const addedCount = result.tracks.length;
                
                result.tracks.forEach(track => {
                    queue.push({
                        title: track.title,
                        artist: track.artist || 'Artis Tidak Diketahui',
                        url: track.url,
                        duration: track.duration || 180,
                        thumbnail: track.thumbnail || '',
                        requestedBy: 'Lokal User'
                    });
                });

                showNotification(`Berhasil menambahkan ${addedCount} lagu ke antrean.`);
                input.value = '';
                
                // If idle, auto-play the first track
                if (!currentTrack && queue.length > 0) {
                    startTrack(queue.shift());
                } else {
                    renderQueue();
                }
                saveToStorage();
            } else {
                showNotification(result.error || 'Lagu tidak ditemukan.', 'error');
            }
        } catch (error) {
            console.error(error);
            showNotification('Gagal menghubungkan ke pencarian bot.', 'error');
        } finally {
            input.disabled = false;
            btn.disabled = false;
            spinner.classList.add('hidden');
            btnText.textContent = 'Tambahkan ke Antrean';
        }
    }

    // Play a specific track
    function startTrack(track) {
        if (currentTrack) {
            history.push(currentTrack);
            if (history.length > 20) history.shift();
        }

        currentTrack = track;
        playbackTime = 0;
        isPlaying = true;
        
        // Setup native audio source from local stream server
        audio.src = `http://localhost:3000/api/stream?url=${encodeURIComponent(track.url)}`;
        audio.volume = volume / 100;
        audio.play().catch(err => {
            console.warn("Playback blocked by browser autoplay policy:", err.message);
            showNotification("Putar audio diblokir browser. Silakan klik tombol play untuk memulai.", "info");
        });
        
        // UI Updates
        document.getElementById('track-title').textContent = track.title;
        document.getElementById('track-artist').textContent = track.artist;
        
        const coverImg = document.getElementById('track-cover');
        const placeholder = document.getElementById('track-cover-placeholder');
        if (track.thumbnail) {
            coverImg.src = track.thumbnail;
            coverImg.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            coverImg.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        document.getElementById('track-requester-container').classList.remove('hidden');
        document.getElementById('track-requester-container').classList.add('flex');
        document.getElementById('track-requester').textContent = track.requestedBy;

        document.getElementById('playing-badge').textContent = 'Sedang Diputar';
        document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-violet-500/10 border border-violet-500/20 text-xs font-bold text-violet-400 inline-block uppercase tracking-wider shadow-sm';

        document.getElementById('icon-play').classList.add('hidden');
        document.getElementById('icon-pause').classList.remove('hidden');

        updateVisualizer(true);
        renderQueue();
        saveToStorage();
    }

    // Toggle Play/Pause
    function togglePlayPause() {
        if (!currentTrack) {
            if (queue.length > 0) {
                startTrack(queue.shift());
            } else {
                showNotification('Antrean kosong. Silakan cari lagu terlebih dahulu.', 'error');
            }
            return;
        }

        isPlaying = !isPlaying;
        if (isPlaying) {
            audio.play().catch(err => console.warn(err));
            document.getElementById('icon-play').classList.add('hidden');
            document.getElementById('icon-pause').classList.remove('hidden');
            document.getElementById('playing-badge').textContent = 'Sedang Diputar';
            document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-violet-500/10 border border-violet-500/20 text-xs font-bold text-violet-400 inline-block uppercase tracking-wider shadow-sm';
            updateVisualizer(true);
        } else {
            audio.pause();
            document.getElementById('icon-play').classList.remove('hidden');
            document.getElementById('icon-pause').classList.add('hidden');
            document.getElementById('playing-badge').textContent = 'Jeda';
            document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/20 text-xs font-bold text-amber-500 inline-block uppercase tracking-wider';
            updateVisualizer(false);
        }
        saveToStorage();
    }

    // Native Audio Event Listeners
    audio.addEventListener('timeupdate', () => {
        if (currentTrack) {
            playbackTime = Math.floor(audio.currentTime);
            const duration = Math.floor(audio.duration) || currentTrack.duration;

            document.getElementById('progress-time').textContent = formatTime(playbackTime);
            document.getElementById('total-time').textContent = formatTime(duration);
            
            const percent = duration > 0 ? (playbackTime / duration) * 100 : 0;
            document.getElementById('progress-bar-fill').style.width = `${percent}%`;
        }
    });

    audio.addEventListener('ended', () => {
        playNext();
    });

    audio.addEventListener('error', (e) => {
        console.error("Audio streaming error:", e);
        showNotification("Gagal memuat streaming audio dari server.", "error");
        playNext(); // Skip to next track on error
    });

    // Play next track (Skip)
    function playNext() {
        if (queue.length > 0) {
            startTrack(queue.shift());
        } else {
            // End of queue
            stopPlayback();
            showNotification('Semua lagu dalam antrean telah selesai diputar.');
        }
    }

    // Play previous track
    function playPrevious() {
        if (history.length > 0) {
            const prev = history.pop();
            // Put current track back in front of queue
            if (currentTrack) {
                queue.unshift(currentTrack);
            }
            startTrack(prev);
        } else {
            showNotification('Tidak ada riwayat lagu sebelumnya.', 'error');
        }
    }

    // Stop playback
    function stopPlayback() {
        isPlaying = false;
        currentTrack = null;
        playbackTime = 0;
        audio.pause();
        audio.src = '';

        // Reset UI
        document.getElementById('track-title').textContent = 'Tidak ada lagu diputar';
        document.getElementById('track-artist').textContent = 'Cari lagu dan tambahkan ke antrean';
        document.getElementById('track-cover').classList.add('hidden');
        document.getElementById('track-cover-placeholder').classList.remove('hidden');
        document.getElementById('track-requester-container').classList.add('hidden');
        document.getElementById('progress-time').textContent = '0:00';
        document.getElementById('total-time').textContent = '0:00';
        document.getElementById('progress-bar-fill').style.width = '0%';

        document.getElementById('icon-play').classList.remove('hidden');
        document.getElementById('icon-pause').classList.add('hidden');
        document.getElementById('playing-badge').textContent = 'Berhenti';
        document.getElementById('playing-badge').className = 'px-2.5 py-0.5 rounded-md bg-slate-900 border border-slate-800 text-xs font-bold text-slate-500 inline-block uppercase tracking-wider';
        
        updateVisualizer(false);
        renderQueue();
        saveToStorage();
    }

    // Clear whole queue
    function clearQueue() {
        queue = [];
        renderQueue();
        showNotification('Daftar antrean dibersihkan.');
        saveToStorage();
    }

    // Remove single track from queue
    function removeTrack(index) {
        const removed = queue.splice(index, 1)[0];
        renderQueue();
        showNotification(`Lagu "${removed.title}" dihapus dari antrean.`);
        saveToStorage();
    }

    // Render Queue HTML
    function renderQueue() {
        const queueList = document.getElementById('queue-list');
        document.getElementById('queue-length-badge').textContent = `${queue.length} Lagu`;

        if (queue.length > 0) {
            queueList.innerHTML = queue.map((track, index) => `
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
                        <div class="text-xs text-slate-550 truncate">${track.artist}</div>
                    </div>
                    <div class="text-xs font-semibold text-slate-550 pr-1">${formatTime(track.duration)}</div>
                    <button type="button" onclick="removeTrack(${index})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors" title="Hapus lagu ini dari antrean">
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
    }

    // Init UI
    renderQueue();
</script>
@endsection
