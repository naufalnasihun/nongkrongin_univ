<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Kumpulan query analitik untuk dashboard admin.
 * Semua agregasi dibuat portabel (aman untuk MySQL & SQLite).
 */
class DashboardAnalyticsService
{
    /** Status yang tidak dihitung sebagai penjualan. */
    private const BUKAN_PENJUALAN = ['Dibatalkan'];

    /**
     * Tren penjualan harian (pendapatan + jumlah pesanan) selama N hari.
     *
     * @return array{labels: array<int,string>, pendapatan: array<int,float>, pesanan: array<int,int>}
     */
    public function trenPenjualan(int $hari = 14): array
    {
        $mulai = now()->subDays($hari - 1)->startOfDay();

        $rows = Pesanan::query()
            ->where('tanggal', '>=', $mulai)
            ->whereNotIn('status', self::BUKAN_PENJUALAN)
            ->selectRaw('DATE(tanggal) as tgl')
            ->selectRaw('COUNT(*) as pesanan')
            ->selectRaw('SUM(CASE WHEN status_bayar = ? THEN total_harga ELSE 0 END) as pendapatan', [Pesanan::BAYAR_LUNAS])
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy(DB::raw('DATE(tanggal)'))
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->tgl)->toDateString());

        $labels = [];
        $pendapatan = [];
        $pesanan = [];

        for ($i = $hari - 1; $i >= 0; $i--) {
            $tgl = now()->subDays($i)->toDateString();
            $row = $rows->get($tgl);

            $labels[] = Carbon::parse($tgl)->isoFormat('D MMM');
            $pendapatan[] = (float) ($row->pendapatan ?? 0);
            $pesanan[] = (int) ($row->pesanan ?? 0);
        }

        return compact('labels', 'pendapatan', 'pesanan');
    }

    /**
     * Bandingkan periode ini (N hari) vs periode sebelumnya.
     *
     * @return array{pendapatan: array{now: float, prev: float, delta: float}, pesanan: array{now: int, prev: int, delta: float}}
     */
    public function bandingkanPeriode(int $hari = 7): array
    {
        $kiniMulai = now()->subDays($hari - 1)->startOfDay();
        $laluMulai = now()->subDays(($hari * 2) - 1)->startOfDay();
        $laluSelesai = now()->subDays($hari)->endOfDay();

        $ambil = function ($mulai, $selesai = null) {
            $q = Pesanan::where('tanggal', '>=', $mulai)
                ->whereNotIn('status', self::BUKAN_PENJUALAN);

            if ($selesai) {
                $q->where('tanggal', '<=', $selesai);
            }

            return [
                'pendapatan' => (float) (clone $q)->where('status_bayar', Pesanan::BAYAR_LUNAS)->sum('total_harga'),
                'pesanan' => (clone $q)->count(),
            ];
        };

        $now = $ambil($kiniMulai);
        $prev = $ambil($laluMulai, $laluSelesai);

        return [
            'pendapatan' => [
                'now' => $now['pendapatan'],
                'prev' => $prev['pendapatan'],
                'delta' => $this->persenDelta($now['pendapatan'], $prev['pendapatan']),
            ],
            'pesanan' => [
                'now' => $now['pesanan'],
                'prev' => $prev['pesanan'],
                'delta' => $this->persenDelta($now['pesanan'], $prev['pesanan']),
            ],
        ];
    }

    /**
     * Jam tersibuk berdasarkan jumlah pesanan per jam (0-23).
     *
     * @return array<int,array{jam:int,label:string,pesanan:int}>
     */
    public function jamSibuk(int $hari = 30): array
    {
        $mulai = now()->subDays($hari - 1)->startOfDay();

        // Ekstraksi jam dilakukan portabel: ambil timestamp mentah lalu
        // kelompokkan di PHP (aman untuk MySQL, SQLite, dan PostgreSQL).
        $rows = Pesanan::query()
            ->where('tanggal', '>=', $mulai)
            ->whereNotIn('status', self::BUKAN_PENJUALAN)
            ->pluck('tanggal');

        $perJam = [];
        foreach ($rows as $tanggal) {
            $jam = (int) Carbon::parse($tanggal)->format('G');
            $perJam[$jam] = ($perJam[$jam] ?? 0) + 1;
        }

        $hasil = [];
        foreach (range(8, 21) as $jam) {
            $hasil[] = [
                'jam' => $jam,
                'label' => str_pad((string) $jam, 2, '0', STR_PAD_LEFT).':00',
                'pesanan' => (int) ($perJam[$jam] ?? 0),
            ];
        }

        return $hasil;
    }

    /**
     * Menu terlaris dalam N hari (top N).
     *
     * @return Collection<int, object>
     */
    public function menuTerlaris(int $hari = 30, int $limit = 5)
    {
        $mulai = now()->subDays($hari - 1)->startOfDay();

        $baris = PesananItem::query()
            ->join('pesanans', 'pesanans.id', '=', 'pesanan_items.pesanan_id')
            ->leftJoin('menus', 'menus.id', '=', 'pesanan_items.menu_id')
            ->where('pesanans.tanggal', '>=', $mulai)
            ->whereNotIn('pesanans.status', self::BUKAN_PENJUALAN)
            ->get([
                'pesanan_items.menu_id',
                'pesanan_items.nama_menu_snapshot',
                'pesanan_items.jumlah',
                'pesanan_items.subtotal',
                'menus.nama_menu as nama_menu_relasi',
            ]);

        return $baris
            ->groupBy(fn ($r) => $r->nama_menu_snapshot ?: ($r->nama_menu_relasi ?: '(menu dihapus)'))
            ->map(fn ($items, $nama) => (object) [
                'nama_menu' => $nama,
                'terjual' => (int) $items->sum('jumlah'),
                'pendapatan' => (float) $items->sum('subtotal'),
            ])
            ->sortByDesc('terjual')
            ->take($limit)
            ->values();
    }

    /**
     * Menu dengan stok rendah (<= ambang) atau habis.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Menu>
     */
    public function stokRendah(int $ambang = 10, int $limit = 8)
    {
        return Menu::with('warung.kantin')
            ->where(function ($q) use ($ambang) {
                $q->where('stok', '<=', $ambang)
                    ->orWhere('ketersediaan', false);
            })
            ->orderBy('stok')
            ->take($limit)
            ->get();
    }

    private function persenDelta(float $now, float $prev): float
    {
        if ($prev <= 0) {
            return $now > 0 ? 100.0 : 0.0;
        }

        return round((($now - $prev) / $prev) * 100, 1);
    }
}
