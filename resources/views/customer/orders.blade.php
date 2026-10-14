@extends('layouts.customer')

@section('title', 'Pesanan Saya')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Pesanan Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Lacak status pesanan Anda di sini.</p>
    </div>
    <a href="{{ route('customer.dashboard') }}" class="btn-outline">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Menu
    </a>
</div>

{{-- Filter riwayat pesanan --}}
<form method="GET" class="card p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:items-end">
        <div class="lg:col-span-2">
            <label class="label">Cari No. Pesanan</label>
            <input type="text" name="search" value="{{ request('search') }}" class="input-field" placeholder="Contoh: ORD-2026...">
        </div>
        <div>
            <label class="label">Status</label>
            <select name="status" class="input-field">
                <option value="">Semua Status</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Pembayaran</label>
            <select name="bayar" class="input-field">
                <option value="">Semua</option>
                <option value="{{ \App\Models\Pesanan::BAYAR_BELUM }}" {{ request('bayar') === \App\Models\Pesanan::BAYAR_BELUM ? 'selected' : '' }}>Belum Bayar</option>
                <option value="{{ \App\Models\Pesanan::BAYAR_LUNAS }}" {{ request('bayar') === \App\Models\Pesanan::BAYAR_LUNAS ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex-1">Filter</button>
            @if (request()->hasAny(['search', 'status', 'bayar', 'dari', 'sampai']))
                <a href="{{ route('customer.orders.index') }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 sm:items-end">
        <div>
            <label class="label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ request('dari') }}" class="input-field">
        </div>
        <div>
            <label class="label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ request('sampai') }}" class="input-field">
        </div>
    </div>
</form>

@php
    $adaFilter = request()->hasAny(['search', 'status', 'bayar', 'dari', 'sampai']);
@endphp

@if ($pesanans->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">{{ $adaFilter ? '🔍' : '📋' }}</div>
        <p class="text-gray-500 font-medium">{{ $adaFilter ? 'Tidak ada pesanan yang cocok' : 'Belum ada pesanan' }}</p>
        <p class="text-sm text-gray-400 mt-1">{{ $adaFilter ? 'Coba ubah filter pencarian Anda.' : 'Yuk, pesan makanan favoritmu sekarang!' }}</p>
        @if ($adaFilter)
            <a href="{{ route('customer.orders.index') }}" class="btn-secondary mt-6">Reset Filter</a>
        @else
            <a href="{{ route('customer.dashboard') }}" class="btn-primary mt-6">Lihat Menu</a>
        @endif
    </div>
@else
    <div class="space-y-4">
        @foreach ($pesanans as $pesanan)
            @php
                $steps = ['Menunggu', 'Diproses', 'Siap Diambil', 'Selesai'];
                $currentStep = array_search($pesanan->status, $steps);
                $badgeClass = match($pesanan->status) {
                    'Diproses' => 'badge-diproses',
                    'Siap Diambil' => 'badge-siap',
                    'Selesai' => 'badge-selesai',
                    default => 'badge-menunggu',
                };
                $stepIcons = ['⏳', '👨‍🍳', '✅', '🎉'];
            @endphp

            <div class="card p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h3 class="font-bold text-gray-900">{{ $pesanan->nomor_pesanan }}</h3>
                            <span class="badge {{ $badgeClass }}">{{ $pesanan->status }}</span>
                            @if ($pesanan->nomor_antrian)
                                <span class="badge bg-emerald-600 text-white">Antrian {{ $pesanan->nomor_antrian }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $pesanan->tanggal->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-lg font-bold text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                        <p class="text-xs {{ $pesanan->status_bayar === \App\Models\Pesanan::BAYAR_LUNAS ? 'text-emerald-600' : 'text-amber-600' }} font-medium">{{ $pesanan->status_bayar }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between">
                        @foreach ($steps as $i => $step)
                            <div class="flex items-center flex-1">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium {{ $i <= $currentStep ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $i <= $currentStep ? $stepIcons[$i] : $i + 1 }}
                                    </div>
                                    <span class="text-xs mt-1.5 text-center {{ $i <= $currentStep ? 'text-emerald-700 font-medium' : 'text-gray-400' }} hidden sm:block">{{ $step }}</span>
                                </div>
                                @if ($i < 3)
                                    <div class="flex-1 h-0.5 mx-1 {{ $i < $currentStep ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach ($pesanan->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="min-w-0">
                                    <span class="text-gray-700">{{ $item->nama_menu }}</span>
                                    @if ($item->menu?->warung)
                                        <p class="text-xs text-emerald-700 font-medium truncate">{{ $item->menu->warung->nama_warung }} · {{ $item->menu->warung->kantin->nama_kantin }}</p>
                                    @endif
                                </div>
                                <span class="text-gray-400 flex-shrink-0">×{{ $item->jumlah }}</span>
                            </div>
                            <span class="text-gray-700 flex-shrink-0 ml-2">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-2 mt-4 pt-3 border-t border-gray-50">
                    <a href="{{ route('customer.orders.struk', $pesanan) }}" target="_blank" class="btn-outline py-1.5 px-3 text-xs">🖨️ Lihat Struk</a>
                    <a href="{{ route('customer.orders.struk.pdf', $pesanan) }}" class="btn-secondary py-1.5 px-3 text-xs">⬇️ PDF</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $pesanans->links() }}
    </div>
@endif

@endsection
