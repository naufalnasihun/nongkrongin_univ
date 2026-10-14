@extends('layouts.admin')

@section('title', 'Management Pesanan')
@section('page-title', 'Management Pesanan')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Daftar Pesanan</h3>
        <p class="text-sm text-gray-500 mt-1">Lihat semua pesanan dari seluruh customer.</p>
    </div>
</div>

<form method="GET" class="card p-4 mb-6">
    <div class="flex flex-col lg:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pesanan atau customer..." class="input-field pl-10">
            </div>
        </div>
        <select name="status" class="input-field lg:w-44">
            <option value="">Semua Status</option>
            @foreach (['Menunggu' => '⏳ Menunggu', 'Diproses' => '👨‍🍳 Diproses', 'Siap Diambil' => '✅ Siap Diambil', 'Selesai' => '🎉 Selesai', 'Dibatalkan' => '✖ Dibatalkan'] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status_bayar" class="input-field lg:w-40">
            <option value="">Semua Pembayaran</option>
            <option value="Belum Bayar" {{ request('status_bayar') === 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
            <option value="Lunas" {{ request('status_bayar') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Reset</a>
    </div>
</form>

<div class="card overflow-hidden">
    {{-- Tabel untuk layar lebar --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="table-header">Nomor Pesanan</th>
                    <th class="table-header">Customer</th>
                    <th class="table-header">Lokasi</th>
                    <th class="table-header">Total</th>
                    <th class="table-header">Pembayaran</th>
                    <th class="table-header">Status</th>
                    <th class="table-header">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($pesanans as $pesanan)
                    @php
                        $badgeClass = match($pesanan->status) {
                            'Diproses' => 'badge-diproses',
                            'Siap Diambil' => 'badge-siap',
                            'Selesai' => 'badge-selesai',
                            'Dibatalkan' => 'badge-habis',
                            default => 'badge-menunggu',
                        };
                        $lokasi = $pesanan->warung
                            ? collect([$pesanan->warung])
                            : $pesanan->items->map(fn ($i) => $i->menu?->warung)->filter()->unique('id');
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900">{{ $pesanan->nomor_pesanan }}</span>
                                @if ($pesanan->nomor_antrian)
                                    <span class="text-xs font-semibold text-emerald-600">{{ $pesanan->nomor_antrian }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="flex flex-col">
                                <span class="text-gray-800">{{ $pesanan->nama_customer }}</span>
                                @if ($pesanan->nim_customer)
                                    <span class="text-xs text-gray-400">{{ $pesanan->nim_customer }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            @if ($lokasi->isNotEmpty())
                                <div class="flex flex-col gap-0.5">
                                    @foreach ($lokasi as $w)
                                        <span class="text-xs text-gray-600">
                                            <span class="font-medium text-emerald-700">{{ $w->nama_warung }}</span>
                                            <span class="text-gray-400">·</span> {{ $w->kantin->nama_kantin ?? '-' }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="table-cell font-medium">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                        <td class="table-cell">
                            @if ($pesanan->status_bayar === 'Lunas')
                                <span class="badge bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700">Belum Bayar</span>
                            @endif
                            <div class="text-[10px] text-gray-400 mt-0.5 uppercase">{{ $pesanan->metode_bayar ?? '-' }}</div>
                        </td>
                        <td class="table-cell"><span class="badge {{ $badgeClass }}">{{ $pesanan->status }}</span></td>
                        <td class="table-cell text-gray-500">{{ $pesanan->tanggal->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-12">
                            <div class="text-4xl mb-2">📋</div>
                            Belum ada pesanan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Kartu untuk layar kecil --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse ($pesanans as $pesanan)
            @php
                $badgeClass = match($pesanan->status) {
                    'Diproses' => 'badge-diproses',
                    'Siap Diambil' => 'badge-siap',
                    'Selesai' => 'badge-selesai',
                    'Dibatalkan' => 'badge-habis',
                    default => 'badge-menunggu',
                };
                $lokasi = $pesanan->warung ? $pesanan->warung->nama_warung : ($pesanan->items->first()?->menu?->warung?->nama_warung ?? '-');
            @endphp
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $pesanan->nomor_pesanan }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $pesanan->nama_customer }}@if ($pesanan->nim_customer) <span class="text-gray-400">·</span> {{ $pesanan->nim_customer }}@endif · {{ $lokasi }}
                        </p>
                    </div>
                    <span class="badge {{ $badgeClass }} flex-shrink-0">{{ $pesanan->status }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if ($pesanan->nomor_antrian)
                            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $pesanan->nomor_antrian }}</span>
                        @endif
                        @if ($pesanan->status_bayar === 'Lunas')
                            <span class="text-xs font-medium text-emerald-600">✓ Lunas</span>
                        @else
                            <span class="text-xs font-medium text-amber-600">Belum bayar</span>
                        @endif
                    </div>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
                <p class="text-[11px] text-gray-400 mt-1.5">{{ $pesanan->tanggal->format('d M Y, H:i') }} · {{ $pesanan->items->count() }} item</p>
            </div>
        @empty
            <div class="text-center text-gray-400 py-12">
                <div class="text-4xl mb-2">📋</div>
                Belum ada pesanan.
            </div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $pesanans->links() }}
</div>

@endsection
