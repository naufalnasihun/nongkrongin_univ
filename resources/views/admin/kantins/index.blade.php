@extends('layouts.admin')

@section('title', 'Management Kantin')
@section('page-title', 'Management Kantin')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Daftar Kantin</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola kantin yang tersedia di kampus.</p>
    </div>
    <a href="{{ route('admin.kantins.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Kantin
    </a>
</div>

@if ($kantins->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">🏫</div>
        <p class="text-gray-500">Belum ada kantin. Klik "Tambah Kantin" untuk menambah.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @foreach ($kantins as $kantin)
            <div class="card overflow-hidden hover:shadow-md transition-shadow duration-300">
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-2xl flex-shrink-0">
                                🏫
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $kantin->nama_kantin }}</h4>
                                @if ($kantin->lokasi)
                                    <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $kantin->lokasi }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($kantin->deskripsi)
                        <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $kantin->deskripsi }}</p>
                    @endif

                    <div class="flex items-center gap-4 mb-4 text-sm">
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <span class="font-semibold text-emerald-700">{{ $kantin->warungs_count }}</span>
                            <span class="text-gray-400">Warung</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <span class="font-semibold text-emerald-700">{{ $kantin->menus_count }}</span>
                            <span class="text-gray-400">Menu</span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.kantins.edit', $kantin) }}" class="btn-secondary flex-1 py-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <form action="{{ route('admin.kantins.destroy', $kantin) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kantin ini? Semua warung dan menu di dalamnya juga akan terhapus.')">
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
@endif

@endsection
