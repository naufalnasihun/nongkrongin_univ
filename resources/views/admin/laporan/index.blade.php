@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Laporan Penjualan</h3>
        <p class="text-sm text-gray-500 mt-1">Periode {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} — {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.laporan.cetak', request()->query()) }}" target="_blank" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zM7 7V3h10v4"/></svg>
            Cetak / PDF
        </a>
        <a href="{{ route('admin.laporan.excel', request()->query()) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
        <a href="{{ route('admin.laporan.csv', request()->query()) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>
</div>

{{-- Filter periode --}}
<form method="GET" class="card p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
        <div>
            <label class="label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}" class="input-field">
        </div>
        <div>
            <label class="label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="input-field">
        </div>
        <button type="submit" class="btn-primary">Terapkan</button>
        <a href="{{ route('admin.laporan.index') }}" class="btn-secondary">Reset</a>
        <div class="flex gap-2 sm:ml-auto">
            <a href="{{ route('admin.laporan.index', ['dari' => now()->toDateString(), 'sampai' => now()->toDateString()]) }}" class="btn-outline py-2 text-xs">Hari ini</a>
            <a href="{{ route('admin.laporan.index', ['dari' => now()->subDays(6)->toDateString(), 'sampai' => now()->toDateString()]) }}" class="btn-outline py-2 text-xs">7 Hari</a>
            <a href="{{ route('admin.laporan.index', ['dari' => now()->subDays(29)->toDateString(), 'sampai' => now()->toDateString()]) }}" class="btn-outline py-2 text-xs">30 Hari</a>
        </div>
    </div>
</form>

{{-- Kartu ringkasan --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($ringkasan['totalPesanan'], 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Pesanan</p>
    </div>
    <div class="stat-card">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-emerald-700">{{ number_format($ringkasan['pesananSelesai'], 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Pesanan Selesai</p>
    </div>
    <div class="stat-card">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center text-white mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-red-600">{{ number_format($ringkasan['pesananDibatalkan'], 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Dibatalkan</p>
    </div>
    <div class="stat-card">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
        </div>
        <p class="text-xl font-bold text-emerald-700">Rp {{ number_format($ringkasan['totalPendapatan'], 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Pendapatan (Lunas) · rata-rata Rp {{ number_format($ringkasan['rataRata'], 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Grafik harian --}}
    <div class="card p-5 lg:col-span-2">
        <h4 class="font-bold text-gray-900 mb-4">Penjualan Harian</h4>
        @php
            // Cast ke float agar aman dari string "0.00" yang lolos dari `?: 1`.
            $maxHarian = (float) ($harian->max('pendapatan') ?: 0);
            if ($maxHarian <= 0) {
                $maxHarian = 1;
            }
        @endphp
        @if ($harian->isEmpty())
            <p class="text-sm text-gray-400 py-8 text-center">Belum ada data pada periode ini.</p>
        @else
            <div class="flex items-end gap-2 h-48 overflow-x-auto pb-2">
                @foreach ($harian as $h)
                    <div class="flex flex-col items-center gap-1 min-w-[42px] flex-shrink-0">
                        <span class="text-[10px] text-gray-500">Rp {{ number_format($h->pendapatan / 1000, 0) }}k</span>
                        <div class="w-8 bg-gradient-to-t from-emerald-600 to-emerald-400 rounded-t"
                             style="height: {{ (float) $h->pendapatan > 0 ? max(($h->pendapatan / $maxHarian) * 140, 6) : 6 }}px;"
                             title="{{ $h->jumlah }} pesanan · Rp {{ number_format($h->pendapatan, 0, ',', '.') }}"></div>
                        <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($h->tanggal)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Menu terlaris --}}
    <div class="card p-5">
        <h4 class="font-bold text-gray-900 mb-4">🍛 Menu Terlaris</h4>
        @if ($menuTerlaris->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">Belum ada data.</p>
        @else
            <div class="space-y-3">
                @foreach ($menuTerlaris as $i => $m)
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $m->nama_menu }}</p>
                            <p class="text-xs text-gray-400">{{ $m->kategori }} · {{ $m->terjual }} terjual</p>
                        </div>
                        <span class="text-sm font-semibold text-emerald-700 flex-shrink-0">Rp {{ number_format($m->pendapatan, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Per warung --}}
    <div class="card p-5">
        <h4 class="font-bold text-gray-900 mb-4">🏪 Penjualan per Warung</h4>
        @if ($perWarung->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">Belum ada data.</p>
        @else
            <div class="space-y-3">
                @foreach ($perWarung as $w)
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $w->warung }}</p>
                            <p class="text-xs text-gray-400">{{ $w->kantin }} · {{ $w->jumlah }} pesanan</p>
                        </div>
                        <span class="text-sm font-semibold text-emerald-700 flex-shrink-0">Rp {{ number_format($w->pendapatan, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Per kantin --}}
    <div class="card p-5 lg:col-span-2">
        <h4 class="font-bold text-gray-900 mb-4">🏬 Penjualan per Kantin</h4>
        @if ($perKantin->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">Belum ada data.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                            <th class="pb-2">Kantin</th>
                            <th class="pb-2 text-right">Jumlah Pesanan</th>
                            <th class="pb-2 text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perKantin as $k)
                            <tr class="border-b border-gray-50 last:border-0">
                                <td class="py-2 font-medium text-gray-800">{{ $k->kantin }}</td>
                                <td class="py-2 text-right text-gray-600">{{ $k->jumlah }}</td>
                                <td class="py-2 text-right font-semibold text-emerald-700">Rp {{ number_format($k->pendapatan, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
