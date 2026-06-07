@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Server Discord Kamu</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Pilih server untuk mengelola pemutaran musik atau hubungkan Yuukaa ke server baru.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto self-start md:self-auto">
            <a href="{{ route('music.play') }}" class="w-full sm:w-auto px-4 py-2.5 sm:py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 hover:scale-[1.02] text-white font-bold text-sm flex items-center justify-center gap-2 transition-all duration-300 shadow-lg shadow-violet-600/15">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Music Player
            </a>
            
            <div class="w-full sm:w-auto flex items-center justify-center gap-3 bg-violet-950/20 border border-violet-500/20 px-4 py-2.5 sm:py-2 rounded-xl text-sm text-violet-300 shadow-md">
                <span class="w-2.5 h-2.5 rounded-full bg-violet-400 animate-pulse shadow-sm shadow-violet-500/50"></span>
                Server bot aktif
            </div>
        </div>
    </div>

    <!-- Servers Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($guilds as $guild)
            <div class="glass-card rounded-2xl p-4 sm:p-5 group flex flex-col justify-between min-h-48 h-auto relative overflow-hidden">
                <!-- Background glow decoration if bot is inside -->
                @if($guild['has_bot'])
                    <div class="absolute -right-12 -bottom-12 w-28 h-28 rounded-full bg-[#A855F7]/8 blur-2xl group-hover:bg-[#A855F7]/15 transition-colors duration-300"></div>
                @else
                    <div class="absolute -right-12 -bottom-12 w-28 h-28 rounded-full bg-[#5865F2]/4 blur-2xl group-hover:bg-[#5865F2]/8 transition-colors duration-300"></div>
                @endif

                <div class="flex items-start gap-3 sm:gap-4">
                    <!-- Guild Icon -->
                    @if($guild['icon'])
                        <img src="https://cdn.discordapp.com/icons/{{ $guild['id'] }}/{{ $guild['icon'] }}.png" 
                             alt="{{ $guild['name'] }}" 
                             class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl shadow-md border border-slate-900 group-hover:scale-105 transition-transform duration-300 shrink-0">
                    @else
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-950 border border-slate-800 flex items-center justify-center font-bold text-base sm:text-lg text-slate-300 group-hover:scale-105 transition-transform duration-300 shrink-0">
                            {{ Str::upper(Str::substr($guild['name'], 0, 2)) }}
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-white truncate group-hover:text-violet-400 transition-colors" title="{{ $guild['name'] }}">
                            {{ $guild['name'] }}
                        </h2>
                        
                        <!-- Status Badge -->
                        <div class="mt-1.5">
                            @if($guild['has_bot'])
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-violet-500/10 text-violet-300 border border-violet-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                                    Terhubung
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-slate-900 text-slate-400 border border-slate-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                    Belum Terhubung
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-900/60 relative z-10">
                    @if($guild['has_bot'])
                        <a href="{{ route('dashboard.guild', $guild['id']) }}" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 hover:scale-[1.02] text-white font-bold text-sm flex items-center justify-center gap-2 transition-all duration-300 shadow-lg shadow-violet-600/15">
                            Kelola Musik
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        @php
                            $clientId = env('DISCORD_CLIENT_ID');
                            $inviteUrl = "https://discord.com/oauth2/authorize?client_id={$clientId}&permissions=3145728&scope=bot%20applications.commands&guild_id={$guild['id']}&disable_guild_select=true";
                        @endphp
                        <a href="{{ $inviteUrl }}" target="_blank" class="w-full py-2.5 rounded-xl border border-violet-500/30 text-violet-300 hover:bg-violet-500/10 hover:border-violet-500/50 hover:scale-[1.02] font-bold text-sm flex items-center justify-center gap-2 transition-all duration-300 shadow-md">
                            Undang Bot
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center glass-card rounded-2xl">
                <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="text-xl font-bold text-white mb-1">Tidak Ada Server Ditemukan</h3>
                <p class="text-slate-400 text-sm max-w-md mx-auto">
                    Kami tidak menemukan server Discord di mana Anda memiliki hak akses Administrator atau Mengelola Server.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
