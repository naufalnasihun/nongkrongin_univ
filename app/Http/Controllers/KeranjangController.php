<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\KeranjangItemRequest;
use App\Models\Customer;
use App\Models\Keranjang;
use App\Models\KeranjangItem;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\PesananLog;
use App\Models\Warung;
use App\Services\NomorAntrianService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KeranjangController extends Controller
{
    private function getCustomerId(): int
    {
        return Auth::user()->customer->id;
    }

    private function getKeranjang(): Keranjang
    {
        return Keranjang::firstOrCreate([
            'customer_id' => $this->getCustomerId(),
        ]);
    }

    public function add(KeranjangItemRequest $request, Menu $menu)
    {
        $jumlah = (int) $request->validated()['jumlah'];

        if (! $menu->ketersediaan) {
            return back()->with('error', 'Menu ini sedang tidak tersedia.');
        }

        $keranjang = $this->getKeranjang();

        // Cegah quantity keranjang melebihi stok yang tersedia.
        $sisaStok = $menu->stok - $this->jumlahDiKeranjang($keranjang->id, $menu->id);
        $sisaStok = max($sisaStok, 0);

        if ($jumlah > $sisaStok) {
            return back()->with('error', "Stok {$menu->nama_menu} tersisa {$sisaStok}. Tidak dapat menambah {$jumlah}.");
        }

        DB::transaction(function () use ($keranjang, $menu, $jumlah) {
            $existingItem = KeranjangItem::where('keranjang_id', $keranjang->id)
                ->where('menu_id', $menu->id)
                ->lockForUpdate()
                ->first();

            if ($existingItem) {
                $existingItem->jumlah += $jumlah;
                $existingItem->subtotal = $existingItem->jumlah * $menu->harga;
                $existingItem->save();
            } else {
                KeranjangItem::create([
                    'keranjang_id' => $keranjang->id,
                    'menu_id' => $menu->id,
                    'jumlah' => $jumlah,
                    'subtotal' => $jumlah * $menu->harga,
                ]);
            }
        });

        return back()->with('success', "{$menu->nama_menu} ditambahkan ke keranjang.");
    }

    public function update(KeranjangItemRequest $request, KeranjangItem $item)
    {
        $keranjang = $this->getKeranjang();

        if ($item->keranjang_id !== $keranjang->id) {
            abort(403);
        }

        $jumlah = (int) $request->validated()['jumlah'];
        $menu = $item->menu;

        if (! $menu || ! $menu->ketersediaan) {
            return back()->with('error', 'Menu tidak tersedia.');
        }

        if ($jumlah > $menu->stok) {
            return back()->with('error', "Stok {$menu->nama_menu} hanya {$menu->stok}.");
        }

        $item->jumlah = $jumlah;
        $item->subtotal = $jumlah * $menu->harga;
        $item->save();

        return back()->with('success', 'Jumlah diperbarui.');
    }

    public function remove(KeranjangItem $item)
    {
        $keranjang = $this->getKeranjang();

        if ($item->keranjang_id !== $keranjang->id) {
            abort(403);
        }

        $item->delete();

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function checkout(CheckoutRequest $request)
    {
        $customerId = $this->getCustomerId();
        $metodeBayar = $request->validated('metode_bayar');
        $catatan = $request->validated('catatan');

        try {
            $pesanan = DB::transaction(function () use ($customerId, $metodeBayar, $catatan) {
                // Lock keranjang agar tidak ada perubahan bersamaan.
                $keranjang = Keranjang::where('customer_id', $customerId)
                    ->lockForUpdate()
                    ->first();

                $items = $keranjang
                    ? KeranjangItem::where('keranjang_id', $keranjang->id)
                        ->lockForUpdate()
                        ->get()
                    : collect();

                if ($items->isEmpty()) {
                    throw new \RuntimeException('Keranjang Anda kosong.');
                }

                // Lock baris menu (SELECT ... FOR UPDATE) supaya decrement stok
                // dan validasi ketersediaan bersifat atomik -> mencegah oversell.
                $menuIds = $items->pluck('menu_id')->all();
                $menus = Menu::whereIn('id', $menuIds)->lockForUpdate()->get()->keyBy('id');

                $totalHarga = 0;
                $barisPesanan = [];

                foreach ($items as $item) {
                    $menu = $menus->get($item->menu_id);

                    if (! $menu || ! $menu->ketersediaan) {
                        throw new \RuntimeException('Menu '.($menu?->nama_menu ?? 'tidak ditemukan').' tidak tersedia.');
                    }

                    if ($menu->stok < $item->jumlah) {
                        throw new \RuntimeException("Stok {$menu->nama_menu} tidak cukup (tersisa {$menu->stok}).");
                    }

                    $harga = $menu->harga;
                    $subtotal = $harga * $item->jumlah;
                    $totalHarga += $subtotal;

                    $barisPesanan[] = [
                        'menu' => $menu,
                        'jumlah' => $item->jumlah,
                        'harga' => $harga,
                        'subtotal' => $subtotal,
                    ];
                }

                // Semua item harus dari warung yang sama agar antrian jelas.
                $warungIds = collect($barisPesanan)->pluck('menu.warung_id')->unique();
                if ($warungIds->count() > 1) {
                    throw new \RuntimeException('Pesanan hanya boleh berisi menu dari satu warung. Pisahkan pesanan Anda.');
                }

                $warungId = $warungIds->first();
                $warung = $warungId ? Warung::find($warungId) : null;

                $antrian = $warung
                    ? app(NomorAntrianService::class)->generate($warung)
                    : null;

                // Snapshot identitas customer saat pesanan dibuat supaya
                // riwayat pesanan/struk tidak berubah bila data customer diedit.
                $customer = Customer::find($customerId);

                $pesanan = Pesanan::create([
                    'customer_id' => $customerId,
                    'nama_customer_snapshot' => $customer?->nama,
                    'nim_snapshot' => $customer?->nim,
                    'warung_id' => $warungId,
                    'nomor_pesanan' => $this->generateNomorPesanan(),
                    'nomor_antrian' => $antrian,
                    'tanggal' => now(),
                    'total_harga' => $totalHarga,
                    'status' => Pesanan::STATUS_MENUNGGU,
                    'metode_bayar' => $metodeBayar,
                    'status_bayar' => Pesanan::BAYAR_BELUM,
                ]);

                foreach ($barisPesanan as $baris) {
                    PesananItem::create([
                        'pesanan_id' => $pesanan->id,
                        'menu_id' => $baris['menu']->id,
                        'nama_menu_snapshot' => $baris['menu']->nama_menu,
                        'jumlah' => $baris['jumlah'],
                        'harga' => $baris['harga'],
                        'harga_snapshot' => $baris['harga'],
                        'subtotal' => $baris['subtotal'],
                    ]);

                    // Decrement stok secara atomik.
                    $baris['menu']->decrement('stok', $baris['jumlah']);

                    if ($baris['menu']->fresh()->stok <= 0) {
                        $baris['menu']->update(['ketersediaan' => false]);
                    }
                }

                PesananLog::create([
                    'pesanan_id' => $pesanan->id,
                    'user_id' => Auth::id(),
                    'dari_status' => null,
                    'ke_status' => Pesanan::STATUS_MENUNGGU,
                    'catatan' => $catatan
                        ? "Pesanan dibuat oleh customer. Catatan: {$catatan}"
                        : 'Pesanan dibuat oleh customer.',
                ]);

                // Kosongkan keranjang.
                KeranjangItem::where('keranjang_id', $keranjang->id)->delete();

                return $pesanan;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('customer.orders.index')
            ->with('success', "Pesanan {$pesanan->nomor_pesanan} berhasil dibuat! Silakan tunggu konfirmasi kasir.");
    }

    /**
     * Nomor pesanan unik dengan sequence harian: NRG-YYYYMMDD-0001.
     */
    private function generateNomorPesanan(): string
    {
        $prefix = 'NRG-'.now()->format('Ymd').'-';

        $urutan = Pesanan::where('nomor_pesanan', 'like', $prefix.'%')
            ->lockForUpdate()
            ->count() + 1;

        // Retry bila ternyata bentrok (race antar transaksi).
        for ($i = 0; $i < 5; $i++) {
            $kandidat = $prefix.str_pad((string) ($urutan + $i), 4, '0', STR_PAD_LEFT);

            if (! Pesanan::where('nomor_pesanan', $kandidat)->exists()) {
                return $kandidat;
            }
        }

        return $prefix.now()->format('Hisv');
    }

    private function jumlahDiKeranjang(int $keranjangId, int $menuId): int
    {
        return (int) KeranjangItem::where('keranjang_id', $keranjangId)
            ->where('menu_id', $menuId)
            ->sum('jumlah');
    }
}
