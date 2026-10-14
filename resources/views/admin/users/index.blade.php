@extends('layouts.admin')

@section('title', 'Management Pengguna')
@section('page-title', 'Management Pengguna')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Daftar Pengguna</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola akun Admin, Kasir, dan Customer.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Pengguna
    </a>
</div>

<form method="GET" class="card p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="input-field pl-10">
            </div>
        </div>
        <select name="role" class="input-field sm:w-40">
            <option value="">Semua Role</option>
            <option value="Admin" {{ request('role') === 'Admin' ? 'selected' : '' }}>Admin</option>
            <option value="Kasir" {{ request('role') === 'Kasir' ? 'selected' : '' }}>Kasir</option>
            <option value="Customer" {{ request('role') === 'Customer' ? 'selected' : '' }}>Customer</option>
        </select>
        <button type="submit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Reset</a>
    </div>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="table-header">No</th>
                    <th class="table-header">Nama</th>
                    <th class="table-header">Email</th>
                    <th class="table-header">Role</th>
                    <th class="table-header">Identitas</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($users as $user)
                    @php
                        if ($user->admin) {
                            $role = 'Admin'; $identitas = $user->admin->id_admin; $badge = 'badge-admin';
                        } elseif ($user->kasir) {
                            $role = 'Kasir'; $identitas = $user->kasir->id_kasir; $badge = 'badge-kasir';
                        } elseif ($user->customer) {
                            $role = 'Customer'; $identitas = $user->customer->nim; $badge = 'badge-customer';
                        } else {
                            $role = 'Tanpa Role'; $identitas = '-'; $badge = 'badge-selesai';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell text-gray-500">{{ $loop->iteration }}</td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="table-cell text-gray-600">{{ $user->email }}</td>
                        <td class="table-cell"><span class="{{ $badge }}">{{ $role }}</span></td>
                        <td class="table-cell text-gray-600">{{ $identitas }}</td>
                        <td class="table-cell">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-12">
                            <div class="text-4xl mb-2">👥</div>
                            Belum ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
