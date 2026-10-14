@extends('layouts.admin')

@section('title', 'Management Warung')
@section('page-title', 'Management Warung')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Daftar Warung</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola warung yang ada di setiap kantin.</p>
    </div>
    <a href="{{ route('admin.warungs.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Warung
    </a>
</div>

<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <select name="kantin_id" onchange="this.form.submit()" class="input-field">
                <option value="">Semua Kantin</option>
                @foreach ($kantins as $kantin)
                    <option value="{{ $kantin->id }}" {{ request('kantin_id') == $kantin->id ? 'selected' : '' }}>
                        {{ $kantin->nama_kantin }}
                    </option>
                @endforeach
            </select>
        </div>
        @if (request('kantin_id'))
            <a href="{{ route('admin.warungs.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</div>

@if ($warungs->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">🍜</div>
        <p class="text-gray-500">
            @if (request('kantin_id'))
                Tidak ada warung di kantin ini.
            @else
                Belum ada warung. Klik "Tambah Warung" untuk menambah.
            @endif
        </p>
    </div>
@else
    <div class="overflow-x-auto card">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Warung</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kantin</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemilik</th>
                    <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Menu</th>
                    <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($warungs as $warung)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4">
                            <div class="font-medium text-gray-900">{{ $warung->nama_warung }}</div>
                            @if ($warung->deskripsi)
                                <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $warung->deskripsi }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="badge badge-info">{{ $warung->kantin->nama_kantin }}</span>
                        </td>
                        <td class="py-4 px-4 text-gray-600">{{ $warung->pemilik ?? '-' }}</td>
                        <td class="py-4 px-4 text-center">
                            <span class="font-semibold text-emerald-700">{{ $warung->menus_count }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.warungs.edit', $warung) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.warungs.destroy', $warung) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus warung ini? Menu di dalamnya akan dilepaskan dari warung.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger px-3 py-1.5 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $warungs->links() }}
    </div>
@endif

@endsection
