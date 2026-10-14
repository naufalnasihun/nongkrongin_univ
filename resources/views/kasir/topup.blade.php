@extends('layouts.kasir')

@section('title', 'Konfirmasi Top-Up')
@section('page-title', 'Konfirmasi Top-Up Saldo')

@section('content')

<div class="mb-6">
    <h3 class="text-xl font-bold text-gray-900">Konfirmasi Top-Up Saldo</h3>
    <p class="text-sm text-gray-500 mt-1">Verifikasi pengisian saldo customer. Menyetujui akan menambah saldo customer.</p>
</div>

@if (session('error'))
    <div class="alert-error mb-6"><span>{{ session('error') }}</span></div>
@endif
@if (session('success'))
    <div class="alert-success mb-6"><span>{{ session('success') }}</span></div>
@endif

{{-- Menunggu konfirmasi --}}
<div class="card p-5 mb-6">
    <h4 class="font-bold text-gray-900 mb-4">Menunggu Konfirmasi ({{ $menunggu->count() }})</h4>

    <div class="space-y-3">
        @forelse ($menunggu as $t)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-900">{{ $t->customer->nama }}</p>
                        <span class="text-xs text-gray-500">{{ $t->customer->nim }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">
                        <span class="font-bold text-emerald-700">Rp {{ number_format((float) $t->jumlah, 0, ',', '.') }}</span>
                        · {{ \App\Models\Topup::METODE[$t->metode] ?? $t->metode }}
                        · {{ $t->created_at->format('d M H:i') }}
                    </p>
                    @if ($t->catatan)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $t->catatan }}</p>
                    @endif
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <form action="{{ route('kasir.topup.setujui', $t) }}" method="POST" onsubmit="return confirm('Setujui top-up ini? Saldo customer akan bertambah.')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-primary py-2">Setujui</button>
                    </form>
                    <form action="{{ route('kasir.topup.tolak', $t) }}" method="POST" onsubmit="return confirm('Tolak top-up ini?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-danger py-2">Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 py-8">Tidak ada pengajuan top-up.</p>
        @endforelse
    </div>
</div>

{{-- Riwayat --}}
<div class="card p-5">
    <h4 class="font-bold text-gray-900 mb-4">Riwayat Terbaru</h4>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                    <th class="py-2 pr-3">Waktu</th>
                    <th class="py-2 pr-3">Customer</th>
                    <th class="py-2 pr-3">Jumlah</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2 pr-3">Diproses oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $t)
                    @php
                        $badge = $t->status === 'Disetujui' ? 'badge-tersedia' : 'badge-habis';
                    @endphp
                    <tr class="border-b border-gray-50">
                        <td class="py-2 pr-3 text-gray-500">{{ optional($t->dikonfirmasi_pada)->format('d M H:i') }}</td>
                        <td class="py-2 pr-3 text-gray-700">{{ $t->customer->nama }} <span class="text-gray-400">· {{ $t->customer->nim }}</span></td>
                        <td class="py-2 pr-3 font-semibold text-gray-800">Rp {{ number_format((float) $t->jumlah, 0, ',', '.') }}</td>
                        <td class="py-2 pr-3"><span class="badge {{ $badge }}">{{ $t->status }}</span></td>
                        <td class="py-2 pr-3 text-gray-500">{{ optional($t->dikonfirmasiOleh)->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
