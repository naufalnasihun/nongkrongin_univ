@extends('layouts.'.(Auth::user()->admin ? 'admin' : (Auth::user()->kasir ? 'kasir' : 'customer')))

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')

@php
    $user = Auth::user();
    $role = $user->admin ? 'Admin' : ($user->kasir ? 'Kasir' : 'Customer');
    $roleBadge = $user->admin ? 'badge-admin' : ($user->kasir ? 'badge-kasir' : 'badge-customer');
@endphp

<div class="max-w-3xl mx-auto">

    {{-- Kartu identitas --}}
    <div class="card p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-2xl shadow-sm shadow-emerald-600/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <h3 class="text-lg font-bold text-gray-900 truncate">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                <span class="badge {{ $roleBadge }} mt-1">{{ $role }}</span>
            </div>
        </div>
    </div>

    {{-- Form data profil --}}
    <div class="card p-6 mb-6">
        <h4 class="font-bold text-gray-900 mb-1">Data Profil</h4>
        <p class="text-sm text-gray-500 mb-5">Perbarui informasi akun Anda.</p>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="label">Nama Akun (login)</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input-field" required>
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-field" required>
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            @if ($user->customer)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $user->customer->nama) }}" class="input-field" required>
                        @error('nama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">NIM</label>
                        <input type="text" name="nim" value="{{ old('nim', $user->customer->nim) }}" class="input-field" required>
                        @error('nim') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="label">Kelas</label>
                    <input type="text" name="kelas" value="{{ old('kelas', $user->customer->kelas) }}" class="input-field" placeholder="Contoh: TI-4A">
                    @error('kelas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @elseif ($user->kasir)
                <div>
                    <label class="label">Nama Kasir</label>
                    <input type="text" name="nama" value="{{ old('nama', $user->kasir->nama) }}" class="input-field" required>
                    @error('nama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    {{-- Form ganti password --}}
    <div class="card p-6">
        <h4 class="font-bold text-gray-900 mb-1">Ganti Password</h4>
        <p class="text-sm text-gray-500 mb-5">Minimal 8 karakter. Gunakan kombinasi yang kuat.</p>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="label">Password Saat Ini</label>
                <input type="password" name="current_password" class="input-field" required autocomplete="current-password">
                @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">Password Baru</label>
                    <input type="password" name="password" class="input-field" required autocomplete="new-password">
                    @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="input-field" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn-primary">Ganti Password</button>
        </form>
    </div>
</div>

@endsection
