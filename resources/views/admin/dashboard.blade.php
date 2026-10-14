@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Admin')

@php
    $maxTren = max(array_merge($tren['pendapatan'], [1]));
    $maxJam = max(array_merge(array_column($jamSibuk, 'pesanan'), [1]));
    $totalTerjual = $menuTerlaris->sum('terjual') ?: 1;

    $fmt = fn ($a) => $a >= 0 ? 'text-emerald-600' : 'text-red-600';
    $ikon = fn ($a) => $a >= 0 ? '↑' : '↓';
@endphp

@section('content')

{{-- Kartu ringkasan --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-6">
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-sm shadow-emerald-600/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
            </div>
            <span class="inline-flex items-center gap-0.5 text-xs font-semibold {{ $fmt($perbandingan['pendapatan']['delta']) }}">
                {{ $ikon($perbandingan['pendapatan']['delta']) }} {{ abs($perbandingan['pendapatan']['delta']) }}%
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mt-3">Rp {{ number_format($pendapatan30, 0, ',', '.') }}</p>
        <p class="text-sm text-gray-500 mt-1">Pendapatan (30 hari)</p>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="inline-flex items-center gap-0.5 text-xs font-semibold {{ $fmt($perbandingan['pesanan']['delta']) }}">
                {{ $ikon($perbandingan['pesanan']['delta']) }} {{ abs($perbandingan['pesanan']['delta']) }}%
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mt-3">{{ number_format($totalPesanan, 0, ',', '.') }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Pesanan</p>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            @if ($pesananMenunggu > 0)
                <span class="badge badge-menunggu">{{ $pesananMenunggu }} perlu diproses</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-gray-900 mt-3">{{ $totalUsers }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Pengguna</p>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            @if ($stokRendah->count() > 0)
                <span class="badge badge-habis">{{ $stokRendah->count() }} stok rendah</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-gray-900 mt-3">{{ $totalMenu }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Menu</p>
    </div>
</div>

{{-- Tren penjualan --}}
<div class="card p-5 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h3 class="font-semibold text-gray-800">Tren Pendapatan</h3>
            <p class="text-xs text-gray-500 mt-0.5">Total pendapatan dari pesanan lunas per hari.</p>
        </div>
        <div class="inline-flex rounded-xl bg-gray-100 p-1">
            @foreach ([7, 14, 30] as $opt)
                <a href="{{ route('admin.dashboard', ['hari' => $opt]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $hari === $opt ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $opt }} hari
                </a>
            @endforeach
        </div>
    </div>

    @if (array_sum($tren['pendapatan']) <= 0 && array_sum($tren['pesanan']) <= 0)
        <p class="text-center text-gray-400 py-10">Belum ada data penjualan pada periode ini.</p>
    @else
        <div class="flex items-end gap-1.5 sm:gap-2 h-52 overflow-x-auto pb-2">
            @foreach ($tren['labels'] as $i => $label)
                <div class="flex flex-col items-center gap-1.5 flex-1 min-w-[28px]">
                    <span class="text-[10px] font-semibold text-emerald-700 whitespace-nowrap">
                        {{ $tren['pendapatan'][$i] > 0 ? number_format($tren['pendapatan'][$i] / 1000, 0).'k' : '' }}
                    </span>
                    <div class="w-full max-w-[36px] bg-gray-50 rounded-lg flex items-end h-36"
                         title="{{ $label }}: Rp {{ number_format($tren['pendapatan'][$i], 0, ',', '.') }} · {{ $tren['pesanan'][$i] }} pesanan">
                        <div class="w-full bg-gradient-to-t from-emerald-600 to-emerald-400 rounded-lg transition-all duration-500 hover:from-emerald-700 hover:to-emerald-500"
                             style="height: {{ $tren['pendapatan'][$i] > 0 ? max(($tren['pendapatan'][$i] / $maxTren) * 100, 6) : 3 }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Jam sibuk --}}
    <div class="card p-5 xl:col-span-2">
        <h3 class="font-semibold text-gray-800 mb-1">Jam Tersibuk</h3>
        <p class="text-xs text-gray-500 mb-5">Distribusi pesanan per jam (30 hari terakhir).</p>
        <div class="flex items-end gap-1 sm:gap-1.5 h-40">
            @foreach ($jamSibuk as $j)
                <div class="flex-1 flex flex-col items-center gap-1" title="{{ $j['label'] }} · {{ $j['pesanan'] }} pesanan">
                    <div class="w-full bg-blue-50 rounded-t-md flex items-end h-32">
                        <div class="w-full bg-gradient-to-t from-blue-600 to-blue-400 rounded-t-md transition-all duration-500"
                             style="height: {{ $j['pesanan'] > 0 ? max(($j['pesanan'] / $maxJam) * 100, 8) : 2 }}%"></div>
                    </div>
                    <span class="text-[9px] text-gray-400 -rotate-45 sm:rotate-0">{{ substr($j['label'], 0, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Menu terlaris --}}
    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-1">Menu Terlaris</h3>
        <p class="text-xs text-gray-500 mb-5">30 hari terakhir.</p>
        <div class="space-y-4">
            @forelse ($menuTerlaris as $i => $m)
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm font-medium text-gray-800 truncate">{{ $m->nama_menu }}</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 flex-shrink-0">{{ $m->terjual }}x</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full" style="width: {{ max(($m->terjual / $totalTerjual) * 100, 4) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">Belum ada data.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Stok rendah --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">⚠️ Stok Rendah</h3>
            <a href="{{ route('admin.menus.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Kelola →</a>
        </div>
        <div class="space-y-3">
            @forelse ($stokRendah as $menu)
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $menu->nama_menu }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $menu->warung->nama_warung ?? '-' }}</p>
                    </div>
                    <span class="badge {{ $menu->stok <= 0 || ! $menu->ketersediaan ? 'badge-habis' : 'badge-menunggu' }} flex-shrink-0">
                        {{ $menu->ketersediaan ? 'Sisa '.$menu->stok : 'Habis' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">Semua stok aman 👍</p>
            @endforelse
        </div>
    </div>

    {{-- Pesanan terbaru --}}
    <div class="card p-5 xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Pesanan Terbaru</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="table-header">Nomor</th>
                        <th class="table-header">Customer</th>
                        <th class="table-header">Total</th>
                        <th class="table-header">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($pesananTerbaru as $pesanan)
                        @php
                            $badgeClass = match ($pesanan->status) {
                                'Diproses' => 'badge-diproses',
                                'Siap Diambil' => 'badge-siap',
                                'Selesai' => 'badge-selesai',
                                'Dibatalkan' => 'badge-habis',
                                default => 'badge-menunggu',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="table-cell font-medium text-gray-900">
                                {{ $pesanan->nomor_pesanan }}
                                @if ($pesanan->nomor_antrian)
                                    <span class="text-xs text-emerald-600 ml-1">{{ $pesanan->nomor_antrian }}</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <span class="text-gray-800">{{ $pesanan->nama_customer }}</span>
                                @if ($pesanan->nim_customer)
                                    <span class="block text-xs text-gray-400">{{ $pesanan->nim_customer }}</span>
                                @endif
                            </td>
                            <td class="table-cell font-medium">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                            <td class="table-cell"><span class="badge {{ $badgeClass }}">{{ $pesanan->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-gray-400 py-8">Belum ada pesanan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
