@extends('layouts.admin')

@section('title', 'Management Menu')
@section('page-title', 'Management Menu')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Daftar Menu</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola menu makanan dan minuman kantin.</p>
    </div>
    <div class="flex gap-2">
        <button type="button" id="toggleBulk" class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Atur Stok Massal
        </button>
        <a href="{{ route('admin.menus.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Menu
        </a>
    </div>
</div>

<form method="GET" class="card p-4 mb-6">
    <div class="flex flex-col lg:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama menu..." class="input-field pl-10">
            </div>
        </div>
        <select name="kategori" class="input-field lg:w-36">
            <option value="">Semua Kategori</option>
            @foreach (\App\Models\Menu::KATEGORI as $value => $label)
                <option value="{{ $value }}" {{ request('kategori') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="ketersediaan" class="input-field lg:w-36">
            <option value="">Semua Status</option>
            <option value="tersedia" {{ request('ketersediaan') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
            <option value="habis" {{ request('ketersediaan') === 'habis' ? 'selected' : '' }}>Habis</option>
        </select>
        <select name="kantin_id" class="input-field lg:w-40">
            <option value="">Semua Kantin</option>
            @foreach ($kantins as $kantin)
                <option value="{{ $kantin->id }}" {{ request('kantin_id') == $kantin->id ? 'selected' : '' }}>{{ $kantin->nama_kantin }}</option>
            @endforeach
        </select>
        <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-amber-300 transition-colors">
            <input type="checkbox" name="stok_rendah" value="1" {{ request('stok_rendah') ? 'checked' : '' }} class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
            <span class="text-sm text-gray-700 whitespace-nowrap">⚠️ Stok rendah</span>
        </label>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('admin.menus.index') }}" class="btn-secondary">Reset</a>
    </div>
</form>

@if ($menus->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">🍽️</div>
        <p class="text-gray-500">Belum ada menu yang cocok. Coba ubah filter atau tambah menu baru.</p>
    </div>
@else
    {{-- Panel aksi massal (tersembunyi default) --}}
    <form action="{{ route('admin.menus.bulk-stok') }}" method="POST" id="bulkForm" class="card p-4 mb-6 hidden border-2 border-emerald-200 bg-emerald-50/40">
        @csrf
        @method('PATCH')
        <div class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="lg:flex-1">
                <p class="text-sm font-semibold text-gray-800">Atur Stok Massal</p>
                <p class="text-xs text-gray-500 mt-0.5">Pilih menu di bawah, lalu terapkan. <span id="bulkCount" class="font-semibold text-emerald-700">0 dipilih</span></p>
            </div>
            <div>
                <label class="label">Aksi</label>
                <select name="aksi" class="input-field lg:w-36">
                    <option value="set">Set jadi</option>
                    <option value="tambah">Tambah</option>
                    <option value="kurang">Kurangi</option>
                </select>
            </div>
            <div>
                <label class="label">Jumlah</label>
                <input type="number" name="nilai" value="10" min="0" class="input-field lg:w-28">
            </div>
            <button type="submit" class="btn-primary" id="bulkSubmit" disabled>Terapkan</button>
            <button type="button" id="bulkCancel" class="btn-secondary">Batal</button>
        </div>
        <div id="bulkIds"></div>
    </form>

    <div class="flex items-center justify-between mb-3">
        <p class="text-xs text-gray-400">{{ $menus->total() }} menu ditemukan</p>
        <button type="button" id="selectAll" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Pilih semua di halaman ini</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
        @foreach ($menus as $menu)
            @php $stokRendah = $menu->stok <= 10 || ! $menu->ketersediaan; @endphp
            <div class="card overflow-hidden hover:shadow-md transition-all duration-300 {{ $stokRendah ? 'ring-1 ring-amber-200' : '' }}">
                <div class="relative aspect-video bg-cream flex items-center justify-center overflow-hidden">
                    @if ($menu->foto)
                        <img src="{{ asset('storage/' . $menu->foto) }}" alt="{{ $menu->nama_menu }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-5xl">🍽️</span>
                    @endif

                    {{-- Checkbox pilih massal --}}
                    <label class="bulk-check absolute top-2 left-2 hidden cursor-pointer bg-white/95 backdrop-blur rounded-lg p-1.5 shadow-sm">
                        <input type="checkbox" value="{{ $menu->id }}" data-nama="{{ $menu->nama_menu }}" class="menu-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    </label>

                    <div class="absolute top-2 right-2">
                        @if ($menu->ketersediaan && $menu->stok > 0)
                            <span class="badge-tersedia shadow-sm">Tersedia</span>
                        @else
                            <span class="badge-habis shadow-sm">Habis</span>
                        @endif
                    </div>
                </div>
                <div class="p-4">
                    <h4 class="font-semibold text-gray-900 mb-1 truncate">{{ $menu->nama_menu }}</h4>
                    @if ($menu->warung)
                        <p class="text-xs text-gray-500 mb-2 truncate">
                            <span class="font-medium text-emerald-700">{{ $menu->warung->nama_warung }}</span>
                            <span class="mx-1">•</span>
                            {{ $menu->warung->kantin->nama_kantin }}
                        </p>
                    @endif
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge bg-gray-100 text-gray-600">{{ $menu->labelKategori() }}</span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $menu->stok <= 5 ? 'text-red-600' : ($stokRendah ? 'text-amber-600' : 'text-gray-500') }}">
                            📦 {{ $menu->stok }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-3 min-h-[2.5rem]">{{ $menu->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    <p class="text-lg font-bold text-emerald-700 mb-4">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="btn-secondary flex-1 py-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus menu ini? Riwayat pesanan lama akan tetap tersimpan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger py-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $menus->links() }}
    </div>
@endif

@endsection

@push('scripts')
<script>
    (function () {
        const toggle = document.getElementById('toggleBulk');
        const form = document.getElementById('bulkForm');
        const checks = () => Array.from(document.querySelectorAll('.menu-checkbox'));
        const labels = () => Array.from(document.querySelectorAll('.bulk-check'));
        const countEl = document.getElementById('bulkCount');
        const submitBtn = document.getElementById('bulkSubmit');
        const idsWrap = document.getElementById('bulkIds');
        let active = false;

        function sync() {
            const picked = checks().filter((c) => c.checked);
            countEl.textContent = picked.length + ' dipilih';
            submitBtn.disabled = picked.length === 0;

            // Sinkronkan input hidden menu_ids[] agar ikut ter-submit.
            idsWrap.innerHTML = '';
            picked.forEach((c) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'menu_ids[]';
                input.value = c.value;
                idsWrap.appendChild(input);
            });
        }

        checks().forEach((c) => c.addEventListener('change', sync));

        toggle?.addEventListener('click', () => {
            active = !active;
            form.classList.toggle('hidden', !active);
            labels().forEach((l) => l.classList.toggle('hidden', !active));
            toggle.classList.toggle('btn-primary', active);
            toggle.classList.toggle('btn-outline', !active);
            if (!active) {
                checks().forEach((c) => (c.checked = false));
                sync();
            }
        });

        document.getElementById('bulkCancel')?.addEventListener('click', () => toggle.click());

        document.getElementById('selectAll')?.addEventListener('click', (e) => {
            if (!active) toggle.click();
            const all = checks();
            const semuaTerpilih = all.every((c) => c.checked);
            all.forEach((c) => (c.checked = !semuaTerpilih));
            e.target.textContent = semuaTerpilih ? 'Pilih semua di halaman ini' : 'Batalkan pilihan';
            sync();
        });

        form?.addEventListener('submit', (e) => {
            if (checks().filter((c) => c.checked).length === 0) {
                e.preventDefault();
            }
        });
    })();
</script>
@endpush
