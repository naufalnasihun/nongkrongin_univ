<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Nongkrongin Univ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-cream">

<header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-lg shadow-sm shadow-emerald-600/20 flex-shrink-0">
                🍔
            </div>
            <div class="min-w-0">
                <h1 class="font-bold text-gray-900 leading-tight text-sm sm:text-base truncate">Nongkrongin</h1>
                <p class="text-xs text-gray-500 leading-tight hidden sm:block">Kantin Pintar</p>
            </div>
        </a>

        @php
            $current = request()->route()->getName();
        @endphp

        <nav class="hidden sm:flex items-center gap-1">
            <a href="{{ route('customer.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_contains($current, 'customer.dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-700' }}">Menu</a>
            <a href="{{ route('customer.orders.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_contains($current, 'customer.orders') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-700' }}">Pesanan Saya</a>
            <a href="{{ route('customer.saldo.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_contains($current, 'customer.saldo') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-700' }}">Saldo</a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            @hasSection('header-actions')
                @yield('header-actions')
            @endif

            @include('partials.theme-toggle')

            <div class="hidden sm:flex items-center gap-2">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl px-2 py-1 hover:bg-emerald-50 transition-colors" title="Profil Saya">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-medium text-gray-700 hidden md:inline">{{ Auth::user()->name }}</span>
                </a>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600 transition-colors" title="Logout" aria-label="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 pb-24 sm:pb-6">
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

    <div class="animate-fade-in">
        @yield('content')
    </div>
</main>

{{-- Bottom nav untuk mobile (menggantikan nav atas yang tersembunyi) --}}
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-100 pb-[env(safe-area-inset-bottom)]">
    <div class="grid grid-cols-2">
        <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center gap-0.5 py-2.5 text-xs font-medium transition-colors {{ str_contains($current, 'customer.dashboard') ? 'text-emerald-600' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Menu
        </a>
        <a href="{{ route('customer.orders.index') }}" class="flex flex-col items-center gap-0.5 py-2.5 text-xs font-medium transition-colors {{ str_contains($current, 'customer.orders') ? 'text-emerald-600' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Pesanan
        </a>
    </div>
</nav>

@stack('scripts')
</body>
</html>
