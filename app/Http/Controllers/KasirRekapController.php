<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\PesananItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Rekap harian & tutup buku (closing) kasir.
 *
 * Menampilkan ringkasan penjualan satu hari: total transaksi, pendapatan
 * per metode bayar, jam sibuk, dan rincian menu terjual. Berguna sebagai
 * laporan serah-terima shift kasir.
 */
class KasirRekapController extends Controller
{
    public function index(Request $request)
    {
        // Default ke hari ini, tetapi bisa pilih tanggal lain.
        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->tanggal)->toDateString()
            : today()->toDateString();

        $base = fn () => Pesanan::whereDate('tanggal', $tanggal);

        $totalPesanan = $base()->count();
        $pesananSelesai = $base()->where('status', Pesanan::STATUS_SELESAI)->count();
        $pesananDibatalkan = $base()->where('status', Pesanan::STATUS_DIBATALKAN)->count();
        $lunas = $base()->where('status_bayar', Pesanan::BAYAR_LUNAS)->count();
        $belumBayar = $base()
            ->where('status_bayar', '!=', Pesanan::BAYAR_LUNAS)
            ->where('status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->count();

        $pendapatan = (float) $base()
            ->where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->where('status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->sum('total_harga');

        // Rincian per metode bayar.
        $perMetode = $base()
            ->where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->selectRaw('metode_bayar, COUNT(*) as jumlah')
            ->selectRaw('SUM(total_harga) as total')
            ->groupBy('metode_bayar')
            ->get()
            ->map(fn ($r) => (object) [
                'metode' => $r->metode_bayar,
                'jumlah' => (int) $r->jumlah,
                'total' => (float) $r->total,
            ])
            ->sortByDesc('total')
            ->values();

        // Distribusi transaksi per jam (portabel: hitung di PHP).
        $perJam = $base()
            ->pluck('tanggal')
            ->map(fn ($t) => (int) Carbon::parse($t)->format('G'))
            ->countBy()
            ->sortKeys()
            ->map(fn ($jumlah, $jam) => (object) ['jam' => (int) $jam, 'jumlah' => (int) $jumlah])
            ->values();

        // Menu terjual hari itu (agregasi di PHP agar portabel).
        $menuTerjual = PesananItem::query()
            ->join('pesanans', 'pesanans.id', '=', 'pesanan_items.pesanan_id')
            ->whereDate('pesanans.tanggal', $tanggal)
            ->where('pesanans.status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->get(['pesanan_items.nama_menu_snapshot', 'pesanan_items.jumlah', 'pesanan_items.subtotal'])
            ->groupBy(fn ($r) => $r->nama_menu_snapshot ?: '(menu dihapus)')
            ->map(fn ($items, $nama) => (object) [
                'nama' => $nama,
                'jumlah' => (int) $items->sum('jumlah'),
                'total' => (float) $items->sum('subtotal'),
            ])
            ->sortByDesc('jumlah')
            ->take(15)
            ->values();

        // Daftar transaksi lunas hari itu (untuk verifikasi kas).
        $transaksi = $base()
            ->where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->with('kasir.user')
            ->orderBy('tanggal')
            ->get();

        $ringkasan = [
            'totalPesanan' => $totalPesanan,
            'pesananSelesai' => $pesananSelesai,
            'pesananDibatalkan' => $pesananDibatalkan,
            'lunas' => $lunas,
            'belumBayar' => $belumBayar,
            'pendapatan' => $pendapatan,
            'rataRata' => $lunas > 0 ? round($pendapatan / $lunas) : 0,
        ];

        return view('kasir.rekap', compact(
            'tanggal',
            'ringkasan',
            'perMetode',
            'perJam',
            'menuTerjual',
            'transaksi'
        ));
    }
}
