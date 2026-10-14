@extends('layouts.kasir')

@section('title', 'Rekap Harian')
@section('page-title', 'Rekap Harian Kasir')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Rekap Harian</h3>
        <p class="text-sm text-gray-500 mt-1">Ringkasan & tutup buku untuk {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</p>
    </div>
    <form method="GET" class="flex items-end gap-2">
        <div>
            <label class="label">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="input-field">
        </div>
        <button type="submit" class="btn-primary">Tampilkan</button>
        <a href="{{ route('kasir.rekap', ['tanggal' => now()->toDateString()]) }}" class="btn-secondary">Hari Ini</a>
    </form>
</div>

{{-- Kartu ringkasan --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs text-gray-500">Pendapatan (Lunas)</p>
        <p class="text-2xl font-bold text-emerald-700 mt-1">Rp {{ number_format($ringkasan['pendapatan'], 0, ',', '.') }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-gray-500">Total Pesanan</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $ringkasan['totalPesanan'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-gray-500">Sudah Lunas</p>
        <p class="text-2xl font-bold text-blue-700 mt-1">{{ $ringkasan['lunas'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-gray-500">Belum Bayar</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $ringkasan['belumBayar'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Per metode bayar --}}
    <div class="card p-5">
        <h4 class="font-bold text-gray-900 mb-4">Per Metode Bayar</h4>
        <div class="space-y-3">
            @forelse ($perMetode as $m)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ \App\Models\Pesanan::METODE_BAYAR[$m->metode] ?? $m->metode }}</span>
                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($m->total, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs text-gray-400 -mt-2">{{ $m->jumlah }} transaksi</div>
            @empty
                <p class="text-sm text-gray-400">Belum ada pembayaran lunas.</p>
            @endforelse
        </div>
    </div>

    {{-- Per jam --}}
    <div class="card p-5">
        <h4 class="font-bold text-gray-900 mb-4">Transaksi per Jam</h4>
        @php $maksJam = $perJam->max('jumlah') ?: 1; @endphp
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @forelse ($perJam as $j)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 w-7 text-right">{{ str_pad($j->jam, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="flex-1 h-4 bg-gray-100 rounded-md overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-md" style="width: {{ max(($j->jumlah / $maksJam) * 100, 8) }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 w-5">{{ $j->jumlah }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada transaksi.</p>
            @endforelse
        </div>
    </div>

    {{-- Menu terjual --}}
    <div class="card p-5">
        <h4 class="font-bold text-gray-900 mb-4">Menu Terjual</h4>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @forelse ($menuTerjual as $m)
                <div class="flex items-center justify-between gap-2 text-sm">
                    <span class="text-gray-600 truncate">{{ $m->nama }}</span>
                    <span class="flex-shrink-0 font-semibold text-gray-900">{{ $m->jumlah }}x</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada menu terjual.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Verifikasi transaksi --}}
<div class="card p-5">
    <div class="flex items-center justify-between mb-4">
        <h4 class="font-bold text-gray-900">Transaksi Lunas (Verifikasi Kas)</h4>
        <span class="text-sm font-semibold text-emerald-700">Total: Rp {{ number_format($ringkasan['pendapatan'], 0, ',', '.') }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                    <th class="py-2 pr-3">Jam</th>
                    <th class="py-2 pr-3">No. Pesanan</th>
                    <th class="py-2 pr-3">Customer</th>
                    <th class="py-2 pr-3">Metode</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $t)
                    <tr class="border-b border-gray-50">
                        <td class="py-2 pr-3 text-gray-500">{{ $t->tanggal->format('H:i') }}</td>
                        <td class="py-2 pr-3 font-medium text-gray-800">{{ $t->nomor_pesanan }}</td>
                        <td class="py-2 pr-3 text-gray-600">{{ $t->nama_customer }} <span class="text-gray-400">· {{ $t->nim_customer }}</span></td>
                        <td class="py-2 pr-3 text-gray-600">{{ \App\Models\Pesanan::METODE_BAYAR[$t->metode_bayar] ?? $t->metode_bayar }}</td>
                        <td class="py-2 text-right font-semibold text-emerald-700">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada transaksi lunas pada tanggal ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
