@extends('layouts.customer')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <div class="card p-6 bg-gradient-to-br from-emerald-600 to-emerald-700 text-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 text-8xl opacity-10 select-none">🍔</div>
            <p class="text-emerald-100 text-sm relative">Selamat datang,</p>
            <h2 class="text-2xl font-bold mt-1 relative">{{ $customer->nama }} 👋</h2>
            <div class="flex flex-wrap items-center gap-2 mt-2 relative">
                @if ($customer->nim)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur text-xs font-medium text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h0a2 2 0 012 2v1m-4 0h4"/></svg>
                        NIM: {{ $customer->nim }}
                    </span>
                @endif
                @if ($customer->kelas)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur text-xs font-medium text-white">
                        Kelas {{ $customer->kelas }}
                    </span>
                @endif
            </div>
            <p class="text-emerald-100 text-sm mt-3 relative">Pesan makanan kantin favoritmu tanpa antre. Cepat, praktis, dan nikmat!</p>
        </div>

        @php
            $kantinAktif = request('kantin_id');
            $warungAktif = request('warung_id');
            $warungsKantinAktif = $kantinAktif
                ? ($kantins->firstWhere('id', (int) $kantinAktif)?->warungs ?? collect())
                : collect();
        @endphp

        {{-- Pilih Kantin (primary grouping) --}}
        <div class="card p-4">
            <p class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Pilih Kantin
            </p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('customer.dashboard', request()->except(['kantin_id', 'warung_id'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-all border {{ !$kantinAktif ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300 hover:text-emerald-700' }}">
                    Semua Kantin
                </a>
                @foreach ($kantins as $kantin)
                    <a href="{{ route('customer.dashboard', array_merge(request()->except(['kantin_id', 'warung_id']), ['kantin_id' => $kantin->id])) }}"
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-all border {{ (int) $kantinAktif === $kantin->id ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300 hover:text-emerald-700' }}">
                        {{ $kantin->nama_kantin }}
                        <span class="text-xs opacity-70">({{ $kantin->warungs->count() }})</span>
                    </a>
                @endforeach
            </div>

            {{-- Pilih Warung (muncul bila kantin dipilih) --}}
            @if ($kantinAktif && $warungsKantinAktif->isNotEmpty())
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-sm font-semibold text-gray-800 mb-2">Pilih Warung</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('customer.dashboard', request()->except('warung_id')) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all border {{ !$warungAktif ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-gray-500 border-gray-200 hover:border-emerald-300' }}">
                            Semua Warung
                        </a>
                        @foreach ($warungsKantinAktif as $warung)
                            <a href="{{ route('customer.dashboard', array_merge(request()->except('warung_id'), ['warung_id' => $warung->id])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all border {{ (int) $warungAktif === $warung->id ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-gray-500 border-gray-200 hover:border-emerald-300' }}">
                                {{ $warung->nama_warung }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Pencarian & filter kategori/harga --}}
        <form method="GET" class="card p-4">
            @if (request('kantin_id'))
                <input type="hidden" name="kantin_id" value="{{ request('kantin_id') }}">
            @endif
            @if (request('warung_id'))
                <input type="hidden" name="warung_id" value="{{ request('warung_id') }}">
            @endif

            <div class="flex flex-col sm:flex-row gap-3 mb-3">
                <div class="flex-1">
                    <div class="relative">
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari makanan atau minuman..." class="input-field pl-10">
                    </div>
                </div>
                <select name="sort" class="input-field sm:w-44" onchange="this.form.submit()">
                    <option value="">Terbaru</option>
                    <option value="termurah" {{ request('sort') === 'termurah' ? 'selected' : '' }}>Termurah</option>
                    <option value="termahal" {{ request('sort') === 'termahal' ? 'selected' : '' }}>Termahal</option>
                </select>
                <button type="submit" class="btn-primary sm:w-auto">Cari</button>
            </div>
            <div class="flex gap-2 overflow-x-auto">
                @php
                    $ikonKategori = [
                        'makanan' => '🍛 Makanan',
                        'minuman' => '🥤 Minuman',
                        'snack' => '🍟 Snack',
                        'dessert' => '🍰 Dessert',
                    ];
                @endphp
                <a href="{{ route('customer.dashboard', request()->except('kategori')) }}" class="btn-outline whitespace-nowrap py-2 {{ !request('kategori') ? 'bg-emerald-600 text-white border-emerald-600' : '' }}">Semua</a>
                @foreach (\App\Models\Menu::KATEGORI as $value => $label)
                    <a href="{{ route('customer.dashboard', array_merge(request()->except('kategori'), ['kategori' => $value])) }}" class="btn-outline whitespace-nowrap py-2 {{ request('kategori') === $value ? 'bg-emerald-600 text-white border-emerald-600' : '' }}">{{ $ikonKategori[$value] ?? $label }}</a>
                @endforeach
            </div>
        </form>

        @if ($menus->isEmpty())
            <div class="card p-12 text-center">
                <div class="text-5xl mb-4">🍽️</div>
                <p class="text-gray-500">Tidak ada menu yang tersedia saat ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($menus as $menu)
                    <div class="card overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                        <div class="aspect-video bg-cream flex items-center justify-center overflow-hidden relative">
                            @if ($menu->foto)
                                <img src="{{ asset('storage/' . $menu->foto) }}" alt="{{ $menu->nama_menu }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <span class="text-5xl">🍽️</span>
                            @endif
                            @if ($menu->stok <= 5)
                                <span class="absolute top-2 right-2 badge bg-amber-500 text-white shadow-sm">Sisa {{ $menu->stok }}</span>
                            @else
                                <span class="absolute top-2 right-2 badge-tersedia shadow-sm">Stok {{ $menu->stok }}</span>
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-semibold text-gray-900">{{ $menu->nama_menu }}</h4>
                                <span class="text-[10px] uppercase tracking-wide font-semibold text-gray-400 flex-shrink-0">{{ $menu->labelKategori() }}</span>
                            </div>
                            @if ($menu->warung)
                                <p class="text-xs text-emerald-700 font-medium mt-0.5 truncate">{{ $menu->warung->nama_warung }} • {{ $menu->warung->kantin->nama_kantin }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2 min-h-[2rem]">{{ $menu->deskripsi ?? 'Menu spesial kantin.' }}</p>
                            <div class="flex items-center justify-between mt-3">
                                <p class="text-lg font-bold text-emerald-700">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                                <form action="{{ route('customer.cart.add', $menu) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="jumlah" value="1">
                                    <button type="submit" class="btn-primary py-1.5 px-3 text-xs {{ $menu->stok < 1 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $menu->stok < 1 ? 'disabled' : '' }}>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $menus->links() }}
            </div>
        @endif
    </div>

    <div class="lg:col-span-1">
        <div class="sticky top-20">
            <div class="card p-5" id="cartPanel">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Keranjang
                    </h3>
                    @if ($jumlahItem > 0)
                        <span class="badge-emerald bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-medium">{{ $jumlahItem }} item</span>
                    @endif
                </div>

                @if ($items->isEmpty())
                    <div class="text-center py-8">
                        <div class="text-4xl mb-2">🛒</div>
                        <p class="text-sm text-gray-400">Keranjang masih kosong</p>
                        <p class="text-xs text-gray-400 mt-1">Tambahkan menu untuk memulai pesanan</p>
                    </div>
                @else
                    <div class="space-y-3 max-h-72 overflow-y-auto mb-4">
                        @foreach ($items as $item)
                            <div class="flex items-center gap-3 pb-3 border-b border-gray-50 last:border-0">
                                <div class="w-12 h-12 rounded-lg bg-cream flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    @if ($item->menu?->foto)
                                        <img src="{{ asset('storage/' . $item->menu->foto) }}" alt="{{ $item->menu->nama_menu }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xl">🍽️</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $item->menu?->nama_menu ?? '(menu dihapus)' }}</p>
                                    <p class="text-xs text-gray-500">Rp {{ number_format($item->menu?->harga ?? 0, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <form action="{{ route('customer.cart.update', $item) }}" method="POST" class="flex items-center gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="jumlah" value="{{ $item->jumlah - 1 }}" class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center text-sm" {{ $item->jumlah <= 1 ? 'disabled' : '' }}>−</button>
                                        <span class="w-6 text-center text-sm font-medium">{{ $item->jumlah }}</span>
                                        <button type="submit" name="jumlah" value="{{ $item->jumlah + 1 }}" class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center text-sm">+</button>
                                    </form>
                                    <form action="{{ route('customer.cart.remove', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-600">Total</span>
                            <span class="text-xl font-bold text-emerald-700">Rp {{ number_format($totalKeranjang, 0, ',', '.') }}</span>
                        </div>

                        <form action="{{ route('customer.checkout') }}" method="POST" id="checkoutForm">
                            @csrf

                            {{-- Pilihan metode pembayaran --}}
                            <p class="text-sm font-semibold text-gray-800 mb-2">Metode Pembayaran</p>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                @php
                                    $metodeIcons = ['cash' => '💵', 'qris' => '📱', 'deposit' => '💳'];
                                    $metodeDesc = ['cash' => 'Tunai', 'qris' => 'QRIS', 'deposit' => 'Deposit'];
                                @endphp
                                @foreach (\App\Models\Pesanan::METODE_BAYAR as $key => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="metode_bayar" value="{{ $key }}" class="peer sr-only metode-radio"
                                               {{ old('metode_bayar', 'cash') === $key ? 'checked' : '' }} required>
                                        <div class="flex flex-col items-center gap-1 px-2 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-500 transition-all
                                                    peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                            <span class="text-xl">{{ $metodeIcons[$key] ?? '💰' }}</span>
                                            <span class="text-xs font-semibold">{{ $metodeDesc[$key] ?? $label }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Info metode terpilih --}}
                            <div class="text-xs text-gray-500 bg-gray-50 rounded-lg p-2.5 mb-3">
                                <span data-info="cash" class="hidden">💵 Bayar <b>tunai</b> di kasir saat mengambil pesanan.</span>
                                <span data-info="qris" class="hidden">📱 Scan <b>QRIS</b> di kasir. Tunjukkan kode pembayaran saat mengambil pesanan.</span>
                                <span data-info="deposit" class="hidden">💳 Saldo <b>deposit</b> akan dipotong otomatis oleh kasir.</span>
                            </div>

                            {{-- Catatan opsional --}}
                            <label class="block mb-3">
                                <span class="text-xs font-medium text-gray-600">Catatan (opsional)</span>
                                <textarea name="catatan" rows="2" maxlength="255"
                                          class="input-field mt-1 text-sm resize-none"
                                          placeholder="Contoh: tanpa sambal, extra pedas...">{{ old('catatan') }}</textarea>
                            </label>

                            @error('metode_bayar')
                                <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="btn-primary w-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                Checkout Pesanan
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($jumlahItem > 0)
    {{-- Tombol keranjang mengambang untuk mobile --}}
    <button type="button" id="cartFab"
            class="lg:hidden fixed bottom-20 right-4 z-40 flex items-center gap-3 pl-4 pr-5 py-3 rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/40 active:scale-95 transition-transform">
        <span class="relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-white text-emerald-700 text-[10px] font-bold flex items-center justify-center">{{ $jumlahItem }}</span>
        </span>
        <span class="text-sm font-semibold">Rp {{ number_format($totalKeranjang, 0, ',', '.') }}</span>
    </button>
@endif

<script>
    // Scroll ke panel keranjang saat tombol mengambang ditekan (mobile).
    document.getElementById('cartFab')?.addEventListener('click', () => {
        document.getElementById('cartPanel')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // Toggle info metode pembayaran di form checkout.
    (function () {
        const radios = document.querySelectorAll('.metode-radio');
        const infos = document.querySelectorAll('[data-info]');
        function sync() {
            const picked = document.querySelector('.metode-radio:checked')?.value;
            infos.forEach((el) => el.classList.toggle('hidden', el.dataset.info !== picked));
        }
        radios.forEach((r) => r.addEventListener('change', sync));
        sync();
    })();
</script>

@endsection
