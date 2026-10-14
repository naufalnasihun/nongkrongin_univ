<?php

namespace App\Http\Controllers;

use App\Models\Kantin;
use Illuminate\Http\Request;

class KantinController extends Controller
{
    public function index()
    {
        $kantins = Kantin::withCount('warungs', 'menus')->latest()->get();

        return view('admin.kantins.index', compact('kantins'));
    }

    public function create()
    {
        return view('admin.kantins.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kantin' => 'required|string|max:255|unique:kantins,nama_kantin',
            'lokasi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Kantin::create($validated);

        return redirect()
            ->route('admin.kantins.index')
            ->with('success', 'Kantin berhasil ditambahkan.');
    }

    public function edit(Kantin $kantin)
    {
        return view('admin.kantins.edit', compact('kantin'));
    }

    public function update(Request $request, Kantin $kantin)
    {
        $validated = $request->validate([
            'nama_kantin' => 'required|string|max:255|unique:kantins,nama_kantin,'.$kantin->id,
            'lokasi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $kantin->update($validated);

        return redirect()
            ->route('admin.kantins.index')
            ->with('success', 'Kantin berhasil diperbarui.');
    }

    public function destroy(Kantin $kantin)
    {
        $kantin->delete();

        return redirect()
            ->route('admin.kantins.index')
            ->with('success', 'Kantin berhasil dihapus.');
    }
}
