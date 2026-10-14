@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')

@section('content')

<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar pengguna
    </a>

    <div class="card p-6 sm:p-8">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Edit Pengguna</h3>
        <p class="text-sm text-gray-500 mb-6">Perbarui data pengguna. Password boleh dikosongkan.</p>

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

        @php
            if ($user->admin) { $role = 'Admin'; }
            elseif ($user->kasir) { $role = 'Kasir'; }
            elseif ($user->customer) { $role = 'Customer'; }
            else { $role = ''; }
        @endphp

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="label">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="input-field" required>
            </div>

            <div>
                <label for="email" class="label">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="input-field" required>
            </div>

            <div>
                <label for="password" class="label">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah" class="input-field">
            </div>

            <div>
                <label for="role" class="label">Role <span class="text-red-500">*</span></label>
                <select id="role" name="role" class="input-field" required onchange="toggleRoleFields()">
                    <option value="">-- Pilih Role --</option>
                    <option value="Admin" {{ old('role', $role) == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Kasir" {{ old('role', $role) == 'Kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="Customer" {{ old('role', $role) == 'Customer' ? 'selected' : '' }}>Customer</option>
                </select>
            </div>

            <div id="nama-field" class="hidden">
                <label for="nama" class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $user->admin->nama ?? $user->kasir->nama ?? $user->customer->nama ?? $user->name) }}" class="input-field" required>
            </div>

            <div id="id_admin-field" class="hidden">
                <label for="id_admin" class="label">ID Admin <span class="text-red-500">*</span></label>
                <input type="text" id="id_admin" name="id_admin" value="{{ old('id_admin', $user->admin->id_admin ?? '') }}" class="input-field">
            </div>

            <div id="id_kasir-field" class="hidden">
                <label for="id_kasir" class="label">ID Kasir <span class="text-red-500">*</span></label>
                <input type="text" id="id_kasir" name="id_kasir" value="{{ old('id_kasir', $user->kasir->id_kasir ?? '') }}" class="input-field">
            </div>

            <div id="nim-field" class="hidden">
                <label for="nim" class="label">NIM <span class="text-red-500">*</span></label>
                <input type="text" id="nim" name="nim" value="{{ old('nim', $user->customer->nim ?? '') }}" class="input-field">
            </div>

            <div id="kelas-field" class="hidden">
                <label for="kelas" class="label">Kelas <span class="text-red-500">*</span></label>
                <input type="text" id="kelas" name="kelas" value="{{ old('kelas', $user->customer->kelas ?? '') }}" class="input-field">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-primary flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
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
