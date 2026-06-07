<!DOCTYPE html>
<html lang="id" class="dark h-full bg-[#030206]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Yuukaa Music Bot') - Control Panel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .purple-glow {
            box-shadow: 0 0 60px -10px rgba(168, 85, 247, 0.2);
        }
        /* Premium Glassmorphism */
        .glass-card {
            background: rgba(10, 8, 20, 0.55);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(168, 85, 247, 0.12);
        }
        .glass-card:hover {
            border-color: rgba(168, 85, 247, 0.25);
            box-shadow: 0 10px 30px -15px rgba(168, 85, 247, 0.15);
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-100 bg-[#030206] selection:bg-[#8B5CF6]/30 selection:text-white antialiased">
    <div class="min-h-full flex flex-col justify-between">
        <!-- Header / Nav -->
        <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-[#030206]/85 border-b border-slate-900/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-8">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#7C3AED] via-[#9333EA] to-[#C084FC] flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300 shadow-purple-500/10">
                            <!-- Headphones Icon -->
                            <svg class="w-5.5 h-5.5 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V9a6 6 0 00-12 0v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">
                            Yuukaa<span class="text-[#A855F7]">Bot</span>
                        </span>
                    </a>
                    
                    <nav class="hidden md:flex items-center gap-2">
                        <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 {{ request()->routeIs('home') ? 'bg-violet-500/10 text-violet-300 border border-violet-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            Home
                        </a>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.guild') ? 'bg-violet-500/10 text-violet-300 border border-violet-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('music.play') }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 {{ request()->routeIs('music.play') ? 'bg-violet-500/10 text-violet-300 border border-violet-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            Music Player
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <div class="flex items-center gap-3 bg-[#0a0814]/80 border border-slate-900 px-3.5 py-1.5 rounded-full shadow-md shadow-black/20">
                            <!-- User Avatar -->
                            <img src="{{ Auth::user()->avatar ?? 'https://cdn.discordapp.com/embed/avatars/0.png' }}" 
                                 alt="{{ Auth::user()->name }}"
                                 class="w-7 h-7 rounded-full ring-2 ring-[#A855F7]/40 shadow-md">
                            
                            <span class="text-sm font-medium text-slate-200 hidden sm:inline-block">
                                {{ Auth::user()->name }}
                            </span>
                            
                            <span class="h-4 w-px bg-slate-800 hidden sm:inline-block"></span>
                            
                            <!-- Log Out Button -->
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-violet-400 p-1 rounded-md transition-colors" title="Keluar">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 pt-8 pb-20 md:pb-8 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="pt-6 pb-24 md:pb-6 border-t border-slate-900/60 bg-[#040308]/90 text-center text-sm text-slate-500">
            <div class="max-w-7xl mx-auto px-4">
                <p>&copy; {{ date('Y') }} Yuukaa Music. Created BY Azis Maulana</p>
            </div>
        </footer>
    </div>

    <!-- Mobile Bottom Nav -->
    <nav class="md:hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-[90%] max-w-sm glass-card rounded-2xl px-6 py-3 flex items-center justify-around shadow-2xl border border-violet-500/15">
        <!-- Home Link -->
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition-colors {{ request()->routeIs('home') ? 'text-violet-400' : 'text-slate-400 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Home</span>
        </a>

        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition-colors {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.guild') ? 'text-violet-400' : 'text-slate-400 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span>Dashboard</span>
        </a>
        
        <!-- Music Player Link -->
        <a href="{{ route('music.play') }}" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition-colors {{ request()->routeIs('music.play') ? 'text-violet-400' : 'text-slate-400 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
            </svg>
            <span>Player</span>
        </a>
    </nav>
</body>
</html>
