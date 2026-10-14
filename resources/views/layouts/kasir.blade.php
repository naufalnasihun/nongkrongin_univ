<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Nongkrongin Univ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-cream">

{{-- Sidebar: fixed di desktop (diam saat scroll), slide-in di mobile --}}
<aside id="sidebar" class="app-sidebar fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-100 transform -translate-x-full transition-transform duration-300 ease-out lg:translate-x-0">
    <div class="flex items-center gap-3 h-16 px-5 border-b border-gray-100">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-lg shadow-sm shadow-amber-500/20">
            🧾
        </div>
        <div class="min-w-0">
            <h1 class="font-bold text-gray-900 leading-tight truncate">Nongkrongin</h1>
            <p class="text-xs text-gray-500 leading-tight">Panel Kasir</p>
        </div>
        <button id="sidebarClose" class="lg:hidden ml-auto p-1.5 rounded-lg hover:bg-gray-100 text-gray-400" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="sidebar-nav p-3 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        @php
            $current = request()->route()->getName();
        @endphp

        <a href="{{ route('kasir.dashboard') }}" class="sidebar-link group {{ str_contains($current, 'kasir.dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="truncate">Dashboard</span>
        </a>

        <a href="{{ route('kasir.orders.index') }}" class="sidebar-link group {{ str_contains($current, 'kasir.orders') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span class="truncate">Kelola Pesanan</span>
        </a>

        <a href="{{ route('kasir.rekap') }}" class="sidebar-link group {{ str_contains($current, 'kasir.rekap') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="truncate">Rekap Harian</span>
        </a>

        <a href="{{ route('kasir.topup.index') }}" class="sidebar-link group {{ str_contains($current, 'kasir.topup') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span class="truncate">Top-Up Saldo</span>
        </a>
    </nav>

    @yield('sidebar-extra')
</aside>

<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity duration-300"></div>

<div class="app-content flex-1 flex flex-col min-w-0 min-h-screen">

    <header class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-6">
        <div class="flex items-center gap-2 sm:gap-3">
            <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors" aria-label="Buka menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">@yield('page-title', 'Dashboard')</h2>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @hasSection('header-actions')
                @yield('header-actions')
            @endif

            @include('partials.theme-toggle')

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl p-1 -m-1 hover:bg-gray-50 transition-colors" title="Profil Saya">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-medium text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 leading-tight">Kasir</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white shadow-sm">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600 transition-colors" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </header>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-hidden">
        @if (session('success'))
            <div class="alert-success mb-6 animate-fade-in-up" role="status">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-error mb-6 animate-fade-in-up" role="alert">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="animate-fade-in-up">
            @yield('content')
        </div>
    </main>
</div>

<script>
    (function () {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        const isDesktop = () => window.innerWidth >= 1024;

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            // Hanya kunci scroll saat sidebar mobile terbuka.
            if (!isDesktop()) document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        toggle?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });

        // Pastikan scroll bebas di desktop.
        window.addEventListener('resize', () => {
            if (isDesktop()) closeSidebar();
        });

        if (isDesktop()) document.body.style.overflow = '';
    })();
</script>

@stack('scripts')
</body>
</html>
