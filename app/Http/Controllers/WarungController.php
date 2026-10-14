<?php

namespace App\Http\Controllers;

use App\Models\Kantin;
use App\Models\Warung;
use Illuminate\Http\Request;

class WarungController extends Controller
{
    public function index(Request $request)
    {
        $query = Warung::with('kantin')->withCount('menus')->latest();

        if ($request->filled('kantin_id')) {
            $query->where('kantin_id', $request->kantin_id);
        }

        $warungs = $query->paginate(15)->withQueryString();
        $kantins = Kantin::orderBy('nama_kantin')->get();

        return view('admin.warungs.index', compact('warungs', 'kantins'));
    }

    public function create()
    {
        $kantins = Kantin::orderBy('nama_kantin')->get();

        return view('admin.warungs.create', compact('kantins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kantin_id' => 'required|exists:kantins,id',
            'nama_warung' => 'required|string|max:255',
            'pemilik' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Warung::create($validated);

        return redirect()
            ->route('admin.warungs.index')
            ->with('success', 'Warung berhasil ditambahkan.');
    }

    public function edit(Warung $warung)
    {
        $kantins = Kantin::orderBy('nama_kantin')->get();

        return view('admin.warungs.edit', compact('warung', 'kantins'));
    }

    public function update(Request $request, Warung $warung)
    {
        $validated = $request->validate([
            'kantin_id' => 'required|exists:kantins,id',
            'nama_warung' => 'required|string|max:255',
            'pemilik' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $warung->update($validated);

        return redirect()
            ->route('admin.warungs.index')
            ->with('success', 'Warung berhasil diperbarui.');
    }

    public function destroy(Warung $warung)
    {
        $warung->delete();

        return redirect()
            ->route('admin.warungs.index')
            ->with('success', 'Warung berhasil dihapus.');
    }
}
