@extends('layouts.kasir')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')

@php
    $badgeClass = match($pesanan->status) {
        'Diproses' => 'badge-diproses',
        'Siap Diambil' => 'badge-siap',
        'Selesai' => 'badge-selesai',
        'Dibatalkan' => 'badge-habis',
        default => 'badge-menunggu',
    };
@endphp

<div class="mb-6">
    <a href="{{ route('kasir.orders.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar pesanan
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h3 class="text-xl font-bold text-gray-900">{{ $pesanan->nomor_pesanan }}</h3>
        <span class="badge {{ $badgeClass }}">{{ $pesanan->status }}</span>
        @if ($pesanan->nomor_antrian)
            <span class="badge bg-emerald-600 text-white">Antrian {{ $pesanan->nomor_antrian }}</span>
        @endif
        <span class="badge {{ $pesanan->status_bayar === \App\Models\Pesanan::BAYAR_LUNAS ? 'badge-selesai' : 'badge-menunggu' }}">{{ $pesanan->status_bayar }}</span>
    </div>
    <p class="text-sm text-gray-500 mt-1">{{ $pesanan->tanggal->format('d M Y, H:i') }}</p>
</div>

@php
    // Ambil catatan customer dari log pembuatan pesanan (pesan pertama).
    $logAwal = $pesanan->logs->last();
    $catatanCustomer = null;
    if ($logAwal && $logAwal->catatan && str_contains($logAwal->catatan, 'Catatan:')) {
        $catatanCustomer = trim(explode('Catatan:', $logAwal->catatan, 2)[1]);
    }
@endphp

@if ($catatanCustomer)
    <div class="alert-info mb-6">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
        <div>
            <p class="text-sm font-semibold">Catatan dari customer</p>
            <p class="text-sm">{{ $catatanCustomer }}</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card p-5">
            <h4 class="font-bold text-gray-900 mb-4">Item Pesanan</h4>
            <div class="space-y-3">
                @foreach ($pesanan->items as $item)
                    <div class="flex items-center justify-between text-sm gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 rounded bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-semibold flex-shrink-0">{{ $item->jumlah }}×</span>
                            <div class="min-w-0">
                                <p class="text-gray-800 font-medium truncate">{{ $item->nama_menu }}</p>
                                <p class="text-xs text-gray-400">
                                    Rp {{ number_format($item->harga_satuan, 0, ',', '.') }} / porsi
                                    @if ($item->menu?->warung)
                                        · {{ $item->menu->warung->nama_warung }}
                                    @endif
                                    @if (!$item->menu_id)
                                        · <span class="text-amber-600">menu sudah dihapus</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-gray-700 font-semibold flex-shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                <span class="font-semibold text-gray-700">Total</span>
                <span class="text-lg font-bold text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="card p-5">
            <h4 class="font-bold text-gray-900 mb-4">Riwayat Status</h4>
            @forelse ($pesanan->logs as $log)
                <div class="flex gap-3 pb-4 last:pb-0">
                    <div class="flex flex-col items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></span>
                        <span class="w-px flex-1 bg-gray-200"></span>
                    </div>
                    <div class="pb-1">
                        <p class="text-sm text-gray-800 font-medium">
                            {{ $log->dari_status ?? '(baru)' }} → {{ $log->ke_status }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $log->created_at->format('d M Y, H:i') }}
                            @if ($log->user) · oleh {{ $log->user->name }} @endif
                        </p>
                        @if ($log->catatan)
                            <p class="text-xs text-gray-500 mt-1 italic">"{{ $log->catatan }}"</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada riwayat status.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="card p-5">
            <h4 class="font-bold text-gray-900 mb-3">Pelanggan</h4>
            <p class="text-sm font-medium text-gray-800">{{ $pesanan->nama_customer }}</p>
            <p class="text-xs text-gray-500">{{ $pesanan->nim_customer ?? '' }} {{ $pesanan->customer->kelas ? '· '.$pesanan->customer->kelas : '' }}</p>
        </div>

        <div class="card p-5">
            <h4 class="font-bold text-gray-900 mb-3">Pembayaran</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Metode</span>
                    <span class="font-medium">{{ \App\Models\Pesanan::METODE_BAYAR[$pesanan->metode_bayar] ?? $pesanan->metode_bayar }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status Bayar</span>
                    <span class="font-bold {{ $pesanan->status_bayar === \App\Models\Pesanan::BAYAR_LUNAS ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pesanan->status_bayar }}</span>
                </div>
            </div>

            @if ($pesanan->status_bayar !== \App\Models\Pesanan::BAYAR_LUNAS && !$pesanan->isFinal())
                <form action="{{ route('kasir.orders.paid', $pesanan) }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="label">Metode Bayar</label>
                        <select name="metode_bayar" class="input-field" required>
                            @foreach (\App\Models\Pesanan::METODE_BAYAR as $value => $label)
                                <option value="{{ $value }}" {{ $pesanan->metode_bayar === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-success w-full">💰 Tandai Lunas</button>
                </form>
            @endif

            <a href="{{ route('kasir.orders.struk', $pesanan) }}" target="_blank" class="btn-secondary w-full mt-3">🖨️ Cetak Struk</a>
        </div>

        @if (!$pesanan->isFinal())
            <div class="card p-5">
                <h4 class="font-bold text-gray-900 mb-3">Ubah Status</h4>

                @if (empty($pesanan->statusBerikutnya()))
                    <p class="text-sm text-gray-400">Tidak ada transisi tersedia.</p>
                @else
                    <form action="{{ route('kasir.orders.status', $pesanan) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="label">Status Baru</label>
                            <select name="status" class="input-field" required>
                                @foreach ($pesanan->statusBerikutnya() as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Catatan (opsional)</label>
                            <input type="text" name="catatan" class="input-field" placeholder="Contoh: sudah dibayar cash">
                        </div>
                        <button type="submit" class="btn-primary w-full">Simpan Status</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

@endsection
