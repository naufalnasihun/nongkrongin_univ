<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\Warung;
use App\Services\NomorAntrianService;
use Illuminate\Console\Command;

class BackfillMenuData extends Command
{
    protected $signature = 'menu:backfill {--stok=50 : Stok default untuk menu yang belum punya stok}';

    protected $description = 'Isi kategori, stok default, dan snapshot pesanan lama untuk data yang sudah ada.';

    /**
     * Heuristik kategori berdasarkan nama menu (sekali pakai, untuk data lama).
     */
    private const HEURISTIK = [
        'minuman' => ['es ', 'teh', 'jeruk', 'kopi', 'jus', 'susu', 'air', 'soda', 'smoothie', 'matcha', 'boba'],
        'dessert' => ['puding', 'es krim', 'eskrim', 'kue', 'cake', 'donat', 'brownies', 'pancake', 'roti', 'coklat'],
        'snack' => ['kerupuk', 'seblak', 'gorengan', 'tahu', 'tempe', 'kentang', 'pisang goreng', 'singkong', 'snack'],
    ];

    public function handle(): int
    {
        $stokDefault = (int) $this->option('stok');

        $menuDiperbarui = 0;
        Menu::query()->chunkById(100, function ($menus) use ($stokDefault, &$menuDiperbarui) {
            foreach ($menus as $menu) {
                $perubahan = [];

                if ($menu->stok === null || $menu->stok === 0) {
                    $perubahan['stok'] = $stokDefault;
                }

                $nama = strtolower($menu->nama_menu);
                foreach (self::HEURISTIK as $kategori => $keywords) {
                    foreach ($keywords as $kw) {
                        if (str_contains($nama, $kw)) {
                            $perubahan['kategori'] = $kategori;
                            break 2;
                        }
                    }
                }

                if (! empty($perubahan)) {
                    $menu->update($perubahan);
                    $menuDiperbarui++;
                }
            }
        });

        $this->info("Menu diperbarui: {$menuDiperbarui}");

        // Isi snapshot untuk pesanan lama yang belum punya snapshot.
        $itemDiperbarui = 0;
        PesananItem::with('menu')
            ->whereNull('nama_menu_snapshot')
            ->chunkById(200, function ($items) use (&$itemDiperbarui) {
                foreach ($items as $item) {
                    $harga = $item->menu?->harga ?? $item->harga;

                    $item->update([
                        'nama_menu_snapshot' => $item->menu?->nama_menu ?? '(menu dihapus)',
                        'harga_snapshot' => $harga,
                    ]);
                    $itemDiperbarui++;
                }
            });

        $this->info("Pesanan item diperbarui: {$itemDiperbarui}");

        // Backfill warung_id & nomor_antrian untuk pesanan lama.
        $pesananDiperbarui = 0;
        $antrianService = app(NomorAntrianService::class);
        Pesanan::with('items.menu')
            ->whereNull('warung_id')
            ->orderBy('id')
            ->chunk(100, function ($pesanans) use ($antrianService, &$pesananDiperbarui) {
                foreach ($pesanans as $pesanan) {
                    $warungId = $pesanan->items->first()?->menu?->warung_id;

                    if (! $warungId) {
                        continue;
                    }

                    $warung = Warung::find($warungId);
                    $antrian = $warung && ! $pesanan->nomor_antrian
                        ? $antrianService->generate($warung, $pesanan->tanggal->toDateString())
                        : $pesanan->nomor_antrian;

                    $pesanan->update([
                        'warung_id' => $warungId,
                        'nomor_antrian' => $antrian,
                    ]);
                    $pesananDiperbarui++;
                }
            });

        $this->info("Pesanan (warung/antrian) diperbarui: {$pesananDiperbarui}");
        $this->newLine();
        $this->info('Backfill selesai. Silakan cek ulang kategori menu secara manual bila perlu.');

        return self::SUCCESS;
    }
}
