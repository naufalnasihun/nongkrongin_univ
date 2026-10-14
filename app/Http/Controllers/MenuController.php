<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateStokRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Kantin;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with('warung.kantin');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ketersediaan')) {
            $query->where('ketersediaan', $request->ketersediaan === 'tersedia');
        }

        if ($request->filled('kategori')) {
            $query->kategori($request->kategori);
        }

        if ($request->filled('kantin_id')) {
            $query->whereHas('warung', function ($q) use ($request) {
                $q->where('kantin_id', $request->kantin_id);
            });
        }

        // Filter stok rendah (<= ambang) untuk pemantauan cepat.
        if ($request->filled('stok_rendah')) {
            $ambang = (int) $request->input('ambang', 10);
            $query->where(function ($q) use ($ambang) {
                $q->where('stok', '<=', $ambang)->orWhere('ketersediaan', false);
            });
        }

        $menus = $query->latest()->paginate(12)->withQueryString();
        $kantins = Kantin::orderBy('nama_kantin')->get();

        return view('admin.menus.index', compact('menus', 'kantins'));
    }

    /**
     * Update stok banyak menu sekaligus (set / tambah / kurang).
     */
    public function bulkStok(BulkUpdateStokRequest $request)
    {
        $validated = $request->validated();

        $jumlah = DB::transaction(function () use ($validated) {
            $menus = Menu::whereIn('id', $validated['menu_ids'])->lockForUpdate()->get();

            foreach ($menus as $menu) {
                $stokBaru = match ($validated['aksi']) {
                    'set' => (int) $validated['nilai'],
                    'tambah' => $menu->stok + (int) $validated['nilai'],
                    'kurang' => max($menu->stok - (int) $validated['nilai'], 0),
                };

                $menu->update([
                    'stok' => $stokBaru,
                    // Ketersediaan otomatis: habis bila stok 0.
                    'ketersediaan' => $stokBaru > 0 ? true : false,
                ]);
            }

            return $menus->count();
        });

        $label = ['set' => 'diatur ke', 'tambah' => 'ditambah', 'kurang' => 'dikurangi'][$validated['aksi']];

        return back()->with('success', "Stok {$jumlah} menu berhasil {$label} {$validated['nilai']}.");
    }

    public function create()
    {
        $kantins = Kantin::with('warungs')->orderBy('nama_kantin')->get();

        return view('admin.menus.create', compact('kantins'));
    }

    public function store(StoreMenuRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('menu', 'public');
        }

        Menu::create($validated);

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $kantins = Kantin::with('warungs')->orderBy('nama_kantin')->get();

        return view('admin.menus.edit', compact('menu', 'kantins'));
    }

    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($menu->foto) {
                Storage::disk('public')->delete($menu->foto);
            }
            $validated['foto'] = $request->file('foto')->store('menu', 'public');
        }

        $menu->update($validated);

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        // Bila menu masih dirujuk keranjang aktif, hapus item keranjangnya dulu.
        $menu->keranjangItems()->delete();

        if ($menu->foto) {
            Storage::disk('public')->delete($menu->foto);
        }

        // Line item pesanan lama TIDAK terhapus (FK nullOnDelete + snapshot).
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu berhasil dihapus. Riwayat pesanan lama tetap tersimpan.');
    }
}
