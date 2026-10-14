@extends('layouts.kasir')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Kasir')

@section('sidebar-extra')
    {{-- Status realtime polling --}}
    <div class="px-3 pb-3">
        <div id="kasir-polling"
             data-url="{{ route('kasir.polling') }}"
             data-last-id="{{ $latestId ?? 0 }}"
             data-interval="8000"
             data-sound="on"
             data-auto-refresh="on"
             class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-700">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="font-medium">Realtime aktif</span>
        </div>
    </div>
@endsection

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <a href="{{ route('kasir.orders.index', ['status' => 'Menunggu']) }}" class="stat-card group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">⏳</div>
            <span data-count="masuk" class="badge badge-menunggu hidden">{{ $pesananMasuk }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $pesananMasuk }}</p>
        <p class="text-sm text-gray-500 mt-1">Pesanan Masuk</p>
    </a>

    <a href="{{ route('kasir.orders.index', ['status' => 'Diproses']) }}" class="stat-card group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">👨‍🍳</div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $pesananDiproses }}</p>
        <p class="text-sm text-gray-500 mt-1">Sedang Diproses</p>
    </a>

    <a href="{{ route('kasir.orders.index', ['status' => 'Siap Diambil']) }}" class="stat-card group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">✅</div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $pesananSiap }}</p>
        <p class="text-sm text-gray-500 mt-1">Siap Diambil</p>
    </a>

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-gray-100 flex items-center justify-center text-xl">🎉</div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $pesananSelesaiHariIni }}</p>
        <p class="text-sm text-gray-500 mt-1">Selesai Hari Ini</p>
    </div>
</div>

{{-- Statistik harian kasir --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">

    {{-- Ringkasan uang --}}
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Ringkasan Hari Ini</h3>
            <span class="text-xs text-gray-400">{{ now()->format('d M Y') }}</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3">
                <p class="text-xs text-emerald-700/80">Pendapatan (Lunas)</p>
                <p class="text-lg font-bold text-emerald-700 mt-0.5">Rp {{ number_format($hariIni['pendapatan'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                <p class="text-xs text-gray-500">Total Pesanan</p>
                <p class="text-lg font-bold text-gray-900 mt-0.5">{{ $hariIni['totalPesanan'] }}</p>
            </div>
            <div class="rounded-xl bg-blue-50 border border-blue-100 p-3">
                <p class="text-xs text-blue-700/80">Sudah Lunas</p>
                <p class="text-lg font-bold text-blue-700 mt-0.5">{{ $hariIni['lunas'] }}</p>
            </div>
            <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
                <p class="text-xs text-amber-700/80">Belum Bayar</p>
                <p class="text-lg font-bold text-amber-700 mt-0.5">{{ $hariIni['belumBayar'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
            <span>Rata-rata / pesanan: <b class="text-gray-700">Rp {{ number_format($hariIni['rataRata'], 0, ',', '.') }}</b></span>
            <span>Dibatalkan: <b class="text-gray-700">{{ $hariIni['dibatalkan'] }}</b></span>
        </div>

        {{-- Rincian metode bayar --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-600 mb-2">Metode Pembayaran (Lunas)</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($hariIni['perMetode'] as $kode => $m)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-100 text-xs">
                        <span class="font-medium text-gray-700">{{ \App\Models\Pesanan::METODE_BAYAR[$kode] ?? $kode }}</span>
                        <span class="text-gray-400">·</span>
                        <span class="text-gray-600">{{ $m['jumlah'] }}x</span>
                        <span class="text-gray-400">·</span>
                        <span class="font-semibold text-emerald-700">Rp {{ number_format($m['total'], 0, ',', '.') }}</span>
                    </span>
                @empty
                    <span class="text-xs text-gray-400">Belum ada pembayaran lunas hari ini.</span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Distribusi per jam --}}
    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Pesanan per Jam</h3>
        @php $maksJam = collect($hariIni['perJam'])->max('jumlah') ?: 1; @endphp
        @forelse ($hariIni['perJam'] as $j)
            <div class="flex items-center gap-2 mb-2" title="{{ $j['jumlah'] }} pesanan">
                <span class="text-xs text-gray-400 w-6 text-right">{{ str_pad($j['jam'], 2, '0', STR_PAD_LEFT) }}</span>
                <div class="flex-1 h-5 bg-gray-100 rounded-md overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-md transition-all"
                         style="width: {{ max(($j['jumlah'] / $maksJam) * 100, 8) }}%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700 w-6">{{ $j['jumlah'] }}</span>
            </div>
        @empty
            <p class="text-center text-gray-400 text-sm py-8">Belum ada pesanan hari ini</p>
        @endforelse
    </div>
</div>

<div class="card p-5">
    <div class="flex items-center justify-between mb-5">
        <h3 class="font-semibold text-gray-800">Pesanan Terbaru</h3>
        <a href="{{ route('kasir.orders.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Kelola semua →</a>
    </div>

    <div class="space-y-3">
        @forelse ($pesananTerbaru as $pesanan)
            @php
                $badgeClass = match($pesanan->status) {
                    'Diproses' => 'badge-diproses',
                    'Siap Diambil' => 'badge-siap',
                    'Selesai' => 'badge-selesai',
                    'Dibatalkan' => 'badge-habis',
                    default => 'badge-menunggu',
                };
            @endphp
            <a href="{{ route('kasir.orders.show', $pesanan) }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-gray-50 hover:bg-gray-100/70 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-lg shadow-sm flex-shrink-0">
                        🍽️
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-medium text-gray-900">{{ $pesanan->nomor_pesanan }}</p>
                            @if ($pesanan->nomor_antrian)
                                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $pesanan->nomor_antrian }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ $pesanan->nama_customer }}
                            @if ($pesanan->nim_customer)
                                <span class="text-gray-400">·</span> <span class="font-medium text-gray-600">{{ $pesanan->nim_customer }}</span>
                            @endif
                            <span class="text-gray-400">·</span> {{ $pesanan->tanggal->format('H:i') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="badge {{ $badgeClass }}">{{ $pesanan->status }}</span>
                    <span class="font-semibold text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </a>
        @empty
            <p class="text-center text-gray-400 py-8">Belum ada pesanan</p>
        @endforelse
    </div>
</div>

@endsection
