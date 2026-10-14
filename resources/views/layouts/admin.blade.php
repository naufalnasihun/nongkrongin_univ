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
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-lg shadow-sm shadow-emerald-600/20">
            🍔
        </div>
        <div class="min-w-0">
            <h1 class="font-bold text-gray-900 leading-tight truncate">Nongkrongin</h1>
            <p class="text-xs text-gray-500 leading-tight">Kantin Pintar</p>
        </div>
        <button id="sidebarClose" class="lg:hidden ml-auto p-1.5 rounded-lg hover:bg-gray-100 text-gray-400" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="sidebar-nav p-3 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        @php
            $current = request()->route()->getName();
            $navItems = [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'admin.users.index', 'label' => 'Management Pengguna', 'path' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'admin.kantins.index', 'label' => 'Management Kantin', 'path' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'admin.warungs.index', 'label' => 'Management Warung', 'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'admin.menus.index', 'label' => 'Management Menu', 'path' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'badge' => $lowStockCount ?? 0],
                ['route' => 'admin.orders.index', 'label' => 'Management Pesanan', 'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['route' => 'admin.laporan.index', 'label' => 'Laporan Penjualan', 'path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ];
        @endphp

        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="sidebar-link group {{ str_contains($current, $item['route']) ? 'active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['path'] }}"/></svg>
                <span class="truncate">{{ $item['label'] }}</span>
                @if (! empty($item['badge']) && $item['badge'] > 0)
                    <span class="ml-auto flex-shrink-0 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-red-500 text-white text-xs font-bold" title="{{ $item['badge'] }} menu stok rendah / habis">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</aside>

{{-- Overlay gelap saat sidebar terbuka di mobile --}}
<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity duration-300"></div>

{{-- Konten utama: digeser ke kanan (margin-left) pada desktop --}}
<div class="app-content flex-1 flex flex-col min-w-0 min-h-screen">

    <header class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-6">
        <div class="flex items-center gap-2 sm:gap-3">
            <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors" aria-label="Buka menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">@yield('page-title', 'Dashboard')</h2>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @include('partials.theme-toggle')

            <a href="{{ route('admin.laporan.index') }}" class="hidden sm:flex p-2 rounded-lg hover:bg-emerald-50 text-gray-500 hover:text-emerald-600 transition-colors" title="Laporan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </a>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl p-1 -m-1 hover:bg-gray-50 transition-colors" title="Profil Saya">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-medium text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 leading-tight">Administrator</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white shadow-sm">
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

        function lockScroll() {
            // Hanya kunci scroll saat sidebar mobile terbuka.
            if (!isDesktop()) document.body.style.overflow = 'hidden';
        }
        function unlockScroll() {
            document.body.style.overflow = '';
        }

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            lockScroll();
        }
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            unlockScroll();
        }

        toggle?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });

        // Pastikan scroll tidak pernah terkunci di desktop, dan bersihkan
        // saat berpindah ke lebar desktop.
        window.addEventListener('resize', () => {
            if (isDesktop()) closeSidebar();
        });

        // Safety: bila halaman dimuat di desktop, pastikan overflow bersih.
        if (isDesktop()) unlockScroll();
    })();
</script>

@stack('scripts')
</body>
</html>
