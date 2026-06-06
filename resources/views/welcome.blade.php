<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-[#030206]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Yuukaa Music Bot - Putar Musik Spotify di Discord</title>
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
            .purple-glow {
                box-shadow: 0 0 100px -10px rgba(168, 85, 247, 0.25);
            }
            .indigo-glow {
                box-shadow: 0 0 100px -10px rgba(99, 102, 241, 0.2);
            }
            /* Premium Glassmorphism */
            .glass-card {
                background: rgba(10, 8, 20, 0.45);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(168, 85, 247, 0.08);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .glass-card:hover {
                border-color: rgba(168, 85, 247, 0.25);
                box-shadow: 0 12px 30px -10px rgba(168, 85, 247, 0.15);
                transform: translateY(-4px);
            }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full text-slate-100 bg-[#030206] relative overflow-hidden flex flex-col justify-between antialiased">
        
        <!-- Background Decorative Orbs -->
        <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-violet-600/10 blur-3xl purple-glow"></div>
        <div class="absolute top-1/2 -right-40 w-[500px] h-[500px] rounded-full bg-indigo-600/8 blur-3xl indigo-glow"></div>

        <!-- Header -->
        <header class="w-full max-w-7xl mx-auto px-6 h-20 flex items-center justify-between relative z-10">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#7C3AED] via-[#9333EA] to-[#C084FC] flex items-center justify-center shadow-lg shadow-purple-500/10">
                    <svg class="w-5.5 h-5.5 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V9a6 6 0 00-12 0v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">
                    Yuukaa<span class="text-[#A855F7]">Bot</span>
                </span>
            </div>
            
            <div>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#0d0a1b] border border-violet-500/20 hover:border-violet-500/40 hover:bg-violet-950/20 text-sm font-semibold text-violet-300 transition-all duration-300 shadow-md">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login.discord') }}" class="px-5 py-2.5 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-sm font-semibold flex items-center gap-2 transition-all duration-300 shadow-lg shadow-[#5865F2]/20 hover:-translate-y-0.5">
                        <!-- Discord Icon -->
                        <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 127.14 96.36">
                            <path d="M107.7,8.07A105.15,105.15,0,0,0,77.26,0a77.19,77.19,0,0,0-3.3,6.83A96.67,96.67,0,0,0,53.22,6.83,77.19,77.19,0,0,0,49.88,0,105.15,105.15,0,0,0,19.44,8.07C3.66,31.58-1.86,54.65,1,77.53A105.73,105.73,0,0,0,32,96.36a77.7,77.7,0,0,0,6.63-10.85,69.43,69.43,0,0,1-10.5-5c.88-.65,1.72-1.34,2.51-2a75.58,75.58,0,0,0,73,0c.79.71,1.63,1.4,2.51,2a69.07,69.07,0,0,1-10.5,5A78.37,78.37,0,0,0,102.24,85.5a77.7,77.7,0,0,0,6.63,10.85,105.73,105.73,0,0,0,31-18.83C142.8,49.8,136.21,26.79,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53S36.18,40.36,42.45,40.36,53.88,46,53.88,53,48.72,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.24,60,73.24,53S78.41,40.36,84.69,40.36,96.12,46,96.12,53,91,65.69,84.69,65.69Z"/>
                        </svg>
                        Log in
                    </a>
                @endauth
            </div>
        </header>

        <!-- Main Hero -->
        <main class="flex-1 flex items-center justify-center relative z-10 px-6 py-12">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Music Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-violet-950/20 border border-violet-500/20 text-sm font-semibold text-violet-300 mb-8">
                    <span class="w-2 h-2 rounded-full bg-violet-400 animate-pulse"></span>
                    Spotify Integration Ready
                </div>
                
                <!-- Title -->
                <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold tracking-tight mb-6 leading-none text-white">
                    Dengarkan <span class="bg-gradient-to-r from-[#A855F7] via-[#C084FC] to-[#8B5CF6] bg-clip-text text-transparent">Spotify</span><br class="hidden sm:inline">
                    Di Discord Server Kamu.
                </h1>
                
                <!-- Subtitle -->
                <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Yuukaa adalah bot musik Discord modern dengan Dashboard Web interaktif. Cari lagu, putar playlist Spotify, dan kelola pemutaran musik langsung dari browser kamu.
                </p>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 font-bold text-base transition-all duration-300 shadow-xl shadow-purple-500/25 hover:shadow-purple-500/40 hover:-translate-y-0.5 flex items-center justify-center gap-2.5 text-white">
                            Buka Dashboard Server
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login.discord') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-[#5865F2] hover:bg-[#4752C4] font-bold text-base transition-all duration-300 shadow-xl shadow-[#5865F2]/25 hover:-translate-y-0.5 flex items-center justify-center gap-2.5 text-white">
                            <!-- Discord Icon -->
                            <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 127.14 96.36">
                                <path d="M107.7,8.07A105.15,105.15,0,0,0,77.26,0a77.19,77.19,0,0,0-3.3,6.83A96.67,96.67,0,0,0,53.22,6.83,77.19,77.19,0,0,0,49.88,0,105.15,105.15,0,0,0,19.44,8.07C3.66,31.58-1.86,54.65,1,77.53A105.73,105.73,0,0,0,32,96.36a77.7,77.7,0,0,0,6.63-10.85,69.43,69.43,0,0,1-10.5-5c.88-.65,1.72-1.34,2.51-2a75.58,75.58,0,0,0,73,0c.79.71,1.63,1.4,2.51,2a69.07,69.07,0,0,1-10.5,5A78.37,78.37,0,0,0,102.24,85.5a77.7,77.7,0,0,0,6.63,10.85,105.73,105.73,0,0,0,31-18.83C142.8,49.8,136.21,26.79,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53S36.18,40.36,42.45,40.36,53.88,46,53.88,53,48.72,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.24,60,73.24,53S78.41,40.36,84.69,40.36,96.12,46,96.12,53,91,65.69,84.69,65.69Z"/>
                            </svg>
                            Hubungkan via Discord
                        </a>
                    @endauth
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-20 max-w-4xl text-left">
                    <!-- Feature 1 -->
                    <div class="p-6 rounded-2xl glass-card">
                        <div class="w-11 h-11 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400 mb-4 shadow-sm shadow-violet-500/5">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4.586 14.424c-.18.295-.565.387-.86.207-2.377-1.454-5.37-1.783-8.893-.982-.336.075-.668-.135-.744-.47-.076-.336.135-.668.47-.743 3.856-.88 7.15-.51 9.82 1.127.3.18.388.563.208.858zm1.224-2.72c-.226.367-.707.487-1.074.26-2.72-1.672-6.87-2.157-10.075-1.183-.413.125-.847-.107-.972-.52-.125-.413.108-.847.52-.972 3.664-1.112 8.243-.57 12.34 1.954.368.226.488.707.26 1.074zm.107-2.82c-.273.447-.857.593-1.304.32-3.176-1.89-8.384-2.1-11.39-1.187-.51.155-1.05-.134-1.205-.644-.155-.51.134-1.05.644-1.205 3.57-1.085 9.32-.845 13.015 1.35.447.266.593.85.32 1.304z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2 text-white">Integrasi Spotify</h3>
                        <p class="text-sm text-slate-450">Tempelkan tautan Spotify track, album, atau playlist, dan Yuukaa akan langsung memutarnya di voice channel Discord.</p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="p-6 rounded-2xl glass-card">
                        <div class="w-11 h-11 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 shadow-sm shadow-indigo-500/5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2 text-white">Web Control Panel</h3>
                        <p class="text-sm text-slate-450">Putar, jeda, lewati, dan atur volume musik secara visual. Lihat daftar putar yang sedang berjalan tanpa harus mengetik command di Discord.</p>
                    </div>
                    <!-- Feature 3 -->
                    <div class="p-6 rounded-2xl glass-card">
                        <div class="w-11 h-11 rounded-lg bg-fuchsia-500/10 border border-fuchsia-500/20 flex items-center justify-center text-fuchsia-400 mb-4 shadow-sm shadow-fuchsia-500/5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2 text-white">Setup Sekali Klik</h3>
                        <p class="text-sm text-slate-450">Cukup masuk menggunakan akun Discord, pilih server kamu, undang botnya, dan kamu siap mendengarkan musik bersama teman.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 text-center text-sm text-slate-500 relative z-10 border-t border-slate-900/60 bg-[#040308]/90">
            <p>&copy; {{ date('Y') }} Yuukaa Music. Created BY Azis Maulana</p>
        </footer>
    </body>
</html>
