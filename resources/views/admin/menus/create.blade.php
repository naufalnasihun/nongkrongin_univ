@extends('layouts.admin')

@section('title', 'Tambah Menu')
@section('page-title', 'Tambah Menu')

@section('content')

<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.menus.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar menu
    </a>

    <div class="card p-6 sm:p-8">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Tambah Menu Baru</h3>
        <p class="text-sm text-gray-500 mb-6">Isi form berikut untuk menambahkan menu kantin.</p>

        @if ($errors->any())
            <div class="alert-error mb-6">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-medium">Perbaiki kesalahan berikut:</p>
                    <ul class="text-sm mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="label">Foto Menu</label>
                <div class="flex items-start gap-4">
                    <div id="previewContainer" class="hidden w-32 h-32 rounded-xl overflow-hidden bg-cream flex-shrink-0 border border-gray-200">
                        <img id="preview" src="" alt="Preview" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <label class="btn-outline cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Pilih Foto
                            <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onchange="previewImage(event)">
                        </label>
                        <p class="text-xs text-gray-400 mt-2">JPG, PNG, atau WEBP. Maks 2MB.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="kantin_id" class="label">Pilih Kantin <span class="text-red-500">*</span></label>
                    <select id="kantin_id" name="kantin_id" class="input-field" onchange="updateWarungs(this.value)" required>
                        <option value="">-- Pilih Kantin --</option>
                        @foreach ($kantins as $kantin)
                            <option value="{{ $kantin->id }}" data-warungs="{{ $kantin->warungs->toJson() }}" {{ old('kantin_id') == $kantin->id ? 'selected' : '' }}>
                                {{ $kantin->nama_kantin }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="warung_id" class="label">Pilih Warung <span class="text-red-500">*</span></label>
                    <select id="warung_id" name="warung_id" class="input-field" required>
                        <option value="">-- Pilih Kantin Dulu --</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="nama_menu" class="label">Nama Menu <span class="text-red-500">*</span></label>
                <input type="text" id="nama_menu" name="nama_menu" value="{{ old('nama_menu') }}" placeholder="Contoh: Nasi Goreng Spesial" class="input-field" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="kategori" class="label">Kategori <span class="text-red-500">*</span></label>
                    <select id="kategori" name="kategori" class="input-field" required>
                        @foreach (\App\Models\Menu::KATEGORI as $value => $label)
                            <option value="{{ $value }}" {{ old('kategori', 'makanan') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="harga" class="label">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="harga" name="harga" value="{{ old('harga') }}" placeholder="15000" class="input-field" min="0" required>
                </div>
                <div>
                    <label for="stok" class="label">Stok <span class="text-red-500">*</span></label>
                    <input type="number" id="stok" name="stok" value="{{ old('stok', 0) }}" placeholder="0" class="input-field" min="0" required>
                </div>
            </div>

            <div>
                <label for="ketersediaan" class="label">Status Ketersediaan <span class="text-red-500">*</span></label>
                <select id="ketersediaan" name="ketersediaan" class="input-field" required>
                    <option value="1" {{ old('ketersediaan', '1') == '1' ? 'selected' : '' }}>Tersedia</option>
                    <option value="0" {{ old('ketersediaan') == '0' ? 'selected' : '' }}>Habis</option>
                </select>
            </div>

            <div>
                <label for="deskripsi" class="label">Deskripsi Menu</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi singkat menu..." class="input-field resize-none">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-primary flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Menu
                </button>
                <a href="{{ route('admin.menus.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        const preview = document.getElementById('preview');
        const container = document.getElementById('previewContainer');
        const file = event.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function updateWarungs(kantinId, selectedWarungId = null) {
        const warungSelect = document.getElementById('warung_id');
        const kantinSelect = document.getElementById('kantin_id');
        warungSelect.innerHTML = '<option value="">-- Pilih Warung --</option>';

        if (!kantinId) return;

        const option = kantinSelect.querySelector(`option[value="${kantinId}"]`);
        const warungs = JSON.parse(option.dataset.warungs || '[]');
        warungs.forEach(function (w) {
            const opt = document.createElement('option');
            opt.value = w.id;
            opt.textContent = w.nama_warung;
            if (selectedWarungId && String(selectedWarungId) === String(w.id)) {
                opt.selected = true;
            }
            warungSelect.appendChild(opt);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const initialKantin = @json(old('kantin_id'));
        const initialWarung = @json(old('warung_id'));
        if (initialKantin) {
            updateWarungs(initialKantin, initialWarung);
        }
    });
</script>

@endsection
