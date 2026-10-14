@extends('layouts.customer')

@section('title', 'Saldo & Top-Up')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Saldo & Top-Up</h2>
    <p class="text-sm text-gray-500 mt-1">Isi saldo untuk bayar pesanan lebih cepat tanpa uang tunai.</p>
</div>

@if (session('error'))
    <div class="alert-error mb-6"><span>{{ session('error') }}</span></div>
@endif
@if (session('success'))
    <div class="alert-success mb-6"><span>{{ session('success') }}</span></div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Kartu saldo --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl p-6 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-600/20">
            <p class="text-sm text-emerald-50/90">Saldo Aktif</p>
            <p class="text-3xl font-bold mt-2">Rp {{ number_format((float) $customer->saldo, 0, ',', '.') }}</p>
            <div class="mt-4 pt-4 border-t border-white/20 text-xs text-emerald-50/90">
                <p>{{ $customer->nama }} · {{ $customer->nim }}</p>
            </div>
        </div>

        {{-- Form top-up --}}
        <div class="card p-5 mt-6">
            <h4 class="font-bold text-gray-900 mb-1">Ajukan Top-Up</h4>
            <p class="text-xs text-gray-500 mb-4">Bayar via QRIS/tunai lalu konfirmasi ke kasir.</p>

            <form action="{{ route('customer.saldo.topup') }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="label">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" min="1000" max="2000000" step="1000"
                           value="{{ old('jumlah') }}" class="input-field" placeholder="50000" required>
                    @error('jumlah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nominal cepat --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ([20000, 50000, 100000] as $nominal)
                        <button type="button" onclick="document.querySelector('[name=jumlah]').value = {{ $nominal }}"
                                class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-xs font-medium transition-colors">
                            Rp {{ number_format($nominal, 0, ',', '.') }}
                        </button>
                    @endforeach
                </div>

                <div>
                    <label class="label">Metode</label>
                    <select name="metode" class="input-field" required>
                        <option value="qris" {{ old('metode') === 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="cash" {{ old('metode') === 'cash' ? 'selected' : '' }}>Tunai (di kasir)</option>
                        <option value="transfer" {{ old('metode') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                    @error('metode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Catatan (opsional)</label>
                    <input type="text" name="catatan" value="{{ old('catatan') }}" maxlength="255" class="input-field" placeholder="Contoh: transfer BCA a.n. Budi">
                </div>

                <button type="submit" class="btn-primary w-full">Kirim Pengajuan</button>
            </form>
        </div>
    </div>

    {{-- Riwayat top-up --}}
    <div class="lg:col-span-2">
        <div class="card p-5">
            <h4 class="font-bold text-gray-900 mb-4">Riwayat Top-Up</h4>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                            <th class="py-2 pr-3">Tanggal</th>
                            <th class="py-2 pr-3">Jumlah</th>
                            <th class="py-2 pr-3">Metode</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topups as $t)
                            @php
                                $badge = match($t->status) {
                                    'Disetujui' => 'badge-tersedia',
                                    'Ditolak' => 'badge-habis',
                                    default => 'badge-menunggu',
                                };
                            @endphp
                            <tr class="border-b border-gray-50">
                                <td class="py-2 pr-3 text-gray-500">{{ $t->created_at->format('d M Y H:i') }}</td>
                                <td class="py-2 pr-3 font-semibold text-gray-800">Rp {{ number_format((float) $t->jumlah, 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-gray-600">{{ \App\Models\Topup::METODE[$t->metode] ?? $t->metode }}</td>
                                <td class="py-2 pr-3"><span class="badge {{ $badge }}">{{ $t->status }}</span></td>
                                <td class="py-2 text-gray-500 text-xs">{{ $t->catatan ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada riwayat top-up.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $topups->links() }}</div>
        </div>
    </div>
</div>

@endsection
