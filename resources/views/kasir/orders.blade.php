@extends('layouts.kasir')

@section('title', 'Kelola Pesanan')
@section('page-title', 'Kelola Pesanan')

@section('content')

<div id="kasir-polling"
     data-url="{{ route('kasir.polling') }}"
     data-last-id="{{ $latestId ?? 0 }}"
     data-interval="8000"
     data-sound="on"
     data-auto-refresh="on"></div>

<div class="mb-6">
    <h3 class="text-xl font-bold text-gray-900">Daftar Pesanan</h3>
    <p class="text-sm text-gray-500 mt-1">Kelola dan perbarui status pesanan pelanggan.</p>
</div>

<form method="GET" class="card p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pesanan atau customer..." class="input-field pl-10">
            </div>
        </div>
        <select name="status" class="input-field sm:w-44">
            <option value="">Semua Status</option>
            <option value="Menunggu" {{ request('status') === 'Menunggu' ? 'selected' : '' }}>⏳ Menunggu</option>
            <option value="Diproses" {{ request('status') === 'Diproses' ? 'selected' : '' }}>👨‍🍳 Diproses</option>
            <option value="Siap Diambil" {{ request('status') === 'Siap Diambil' ? 'selected' : '' }}>✅ Siap Diambil</option>
            <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>🎉 Selesai</option>
            <option value="Dibatalkan" {{ request('status') === 'Dibatalkan' ? 'selected' : '' }}>✖ Dibatalkan</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('kasir.orders.index') }}" class="btn-secondary">Reset</a>
    </div>
</form>

@if ($pesanans->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">📋</div>
        <p class="text-gray-500">Tidak ada pesanan.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($pesanans as $pesanan)
            @php
                $badgeClass = match($pesanan->status) {
                    'Diproses' => 'badge-diproses',
                    'Siap Diambil' => 'badge-siap',
                    'Selesai' => 'badge-selesai',
                    default => 'badge-menunggu',
                };
            @endphp

            <div class="card p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h4 class="font-bold text-gray-900">{{ $pesanan->nomor_pesanan }}</h4>
                            <span class="badge {{ $badgeClass }}">{{ $pesanan->status }}</span>
                            @if ($pesanan->nomor_antrian)
                                <span class="badge bg-emerald-600 text-white">Antrian {{ $pesanan->nomor_antrian }}</span>
                            @endif
                            @if ($pesanan->status_bayar === \App\Models\Pesanan::BAYAR_LUNAS)
                                <span class="badge badge-selesai">Lunas</span>
                            @else
                                <span class="badge badge-menunggu">Belum Bayar</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $pesanan->nama_customer }}
                            @if ($pesanan->nim_customer)
                                <span class="text-gray-400">·</span> <span class="font-medium text-gray-600">{{ $pesanan->nim_customer }}</span>
                            @endif
                            <span class="text-gray-400">·</span> {{ $pesanan->tanggal->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-lg font-bold text-emerald-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach ($pesanan->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-6 h-6 rounded bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium flex-shrink-0">{{ $item->jumlah }}</span>
                                <div class="min-w-0">
                                    <span class="text-gray-700">{{ $item->nama_menu }}</span>
                                    @if ($item->menu?->warung)
                                        <p class="text-xs text-emerald-700 font-medium truncate">{{ $item->menu->warung->nama_warung }} · {{ $item->menu->warung->kantin->nama_kantin }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-gray-600 flex-shrink-0 ml-2">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                @if (!$pesanan->isFinal())
                    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-50">
                        @if ($pesanan->status === 'Menunggu')
                            <form action="{{ route('kasir.orders.status', $pesanan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Diproses">
                                <button type="submit" class="btn-warning" onclick="return confirm('Ubah status menjadi Diproses?')">
                                    👨‍🍳 Proses Pesanan
                                </button>
                            </form>
                        @endif

                        @if ($pesanan->status === 'Diproses')
                            <form action="{{ route('kasir.orders.status', $pesanan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Siap Diambil">
                                <button type="submit" class="btn-success" onclick="return confirm('Ubah status menjadi Siap Diambil?')">
                                    ✅ Tandai Siap Diambil
                                </button>
                            </form>
                        @endif

                        @if ($pesanan->status === 'Siap Diambil')
                            <form action="{{ route('kasir.orders.status', $pesanan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Selesai">
                                <button type="submit" class="btn-primary" onclick="return confirm('Konfirmasi pesanan sudah diambil dan selesai?')">
                                    🎉 Selesaikan Pesanan
                                </button>
                            </form>
                        @endif

                        @if ($pesanan->status !== 'Menunggu')
                            <form action="{{ route('kasir.orders.status', $pesanan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Dibatalkan">
                                <button type="submit" class="btn-danger" onclick="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">
                                    ✖ Batalkan
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('kasir.orders.show', $pesanan) }}" class="btn-secondary">Detail</a>
                        <a href="{{ route('kasir.orders.struk', $pesanan) }}" target="_blank" class="btn-outline">🖨️</a>
                    </div>
                @else
                    <div class="flex items-center justify-between gap-2 py-2">
                        <span class="text-sm text-gray-400">
                            @if ($pesanan->status === 'Selesai')
                                ✨ Pesanan ini telah selesai.
                            @else
                                ✖ Pesanan ini dibatalkan.
                            @endif
                        </span>
                        <div class="flex gap-2">
                            <a href="{{ route('kasir.orders.show', $pesanan) }}" class="btn-secondary py-1.5 px-3 text-xs">Detail</a>
                            <a href="{{ route('kasir.orders.struk', $pesanan) }}" target="_blank" class="btn-outline py-1.5 px-3 text-xs">🖨️ Struk</a>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $pesanans->links() }}
    </div>
@endif

@endsection
