@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')

@section('content')

<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar pengguna
    </a>

    <div class="card p-6 sm:p-8">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Tambah Pengguna Baru</h3>
        <p class="text-sm text-gray-500 mb-6">Pilih role dan isi data sesuai role yang dipilih.</p>

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

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="label">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="input-field" required>
            </div>

            <div>
                <label for="email" class="label">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="input-field" required>
            </div>

            <div>
                <label for="password" class="label">Password <span class="text-red-500">*</span></label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" class="input-field" required>
            </div>

            <div>
                <label for="role" class="label">Role <span class="text-red-500">*</span></label>
                <select id="role" name="role" class="input-field" required onchange="toggleRoleFields()">
                    <option value="">-- Pilih Role --</option>
                    <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Kasir" {{ old('role') == 'Kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="Customer" {{ old('role') == 'Customer' ? 'selected' : '' }}>Customer</option>
                </select>
            </div>

            <div id="nama-field" class="hidden">
                <label for="nama" class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" class="input-field" required>
            </div>

            <div id="id_admin-field" class="hidden">
                <label for="id_admin" class="label">ID Admin <span class="text-red-500">*</span></label>
                <input type="text" id="id_admin" name="id_admin" value="{{ old('id_admin') }}" placeholder="Contoh: ADM002" class="input-field">
            </div>

            <div id="id_kasir-field" class="hidden">
                <label for="id_kasir" class="label">ID Kasir <span class="text-red-500">*</span></label>
                <input type="text" id="id_kasir" name="id_kasir" value="{{ old('id_kasir') }}" placeholder="Contoh: KSR002" class="input-field">
            </div>

            <div id="nim-field" class="hidden">
                <label for="nim" class="label">NIM <span class="text-red-500">*</span></label>
                <input type="text" id="nim" name="nim" value="{{ old('nim') }}" placeholder="Nomor Induk Mahasiswa" class="input-field">
            </div>

            <div id="kelas-field" class="hidden">
                <label for="kelas" class="label">Kelas <span class="text-red-500">*</span></label>
                <input type="text" id="kelas" name="kelas" value="{{ old('kelas') }}" placeholder="Contoh: TI-A" class="input-field">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-primary flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Pengguna
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleRoleFields() {
        const role = document.getElementById('role').value;
        const fields = ['nama', 'id_admin', 'id_kasir', 'nim', 'kelas'];

        fields.forEach(f => document.getElementById(f + '-field').classList.add('hidden'));

        if (role) {
            document.getElementById('nama-field').classList.remove('hidden');
        }
        if (role === 'Admin') {
            document.getElementById('id_admin-field').classList.remove('hidden');
        } else if (role === 'Kasir') {
            document.getElementById('id_kasir-field').classList.remove('hidden');
        } else if (role === 'Customer') {
            document.getElementById('nim-field').classList.remove('hidden');
            document.getElementById('kelas-field').classList.remove('hidden');
        }
    }

    toggleRoleFields();
</script>

@endsection
