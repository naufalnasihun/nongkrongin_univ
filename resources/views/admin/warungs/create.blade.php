@extends('layouts.admin')

@section('title', 'Tambah Warung')
@section('page-title', 'Tambah Warung')

@section('content')

<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.warungs.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar warung
    </a>

    <div class="card p-6 sm:p-8">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Tambah Warung Baru</h3>
        <p class="text-sm text-gray-500 mb-6">Isi form berikut untuk menambahkan warung.</p>

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

        <form action="{{ route('admin.warungs.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="kantin_id" class="label">Pilih Kantin <span class="text-red-500">*</span></label>
                <select id="kantin_id" name="kantin_id" class="input-field" required>
                    <option value="">-- Pilih Kantin --</option>
                    @foreach ($kantins as $kantin)
                        <option value="{{ $kantin->id }}" {{ old('kantin_id') == $kantin->id ? 'selected' : '' }}>
                            {{ $kantin->nama_kantin }}{{ $kantin->lokasi ? ' - ' . $kantin->lokasi : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="nama_warung" class="label">Nama Warung <span class="text-red-500">*</span></label>
                <input type="text" id="nama_warung" name="nama_warung" value="{{ old('nama_warung') }}" placeholder="Contoh: Warung Bu Tini" class="input-field" required>
            </div>

            <div>
                <label for="pemilik" class="label">Nama Pemilik</label>
                <input type="text" id="pemilik" name="pemilik" value="{{ old('pemilik') }}" placeholder="Contoh: Bu Tini" class="input-field">
            </div>

            <div>
                <label for="deskripsi" class="label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi singkat warung..." class="input-field resize-none">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-primary flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Warung
                </button>
                <a href="{{ route('admin.warungs.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
