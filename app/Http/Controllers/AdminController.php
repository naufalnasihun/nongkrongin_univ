<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private DashboardAnalyticsService $analytics) {}

    public function dashboard(Request $request)
    {
        // Rentang tren penjualan: 7 / 14 / 30 hari.
        $hari = (int) $request->input('hari', 14);
        if (! in_array($hari, [7, 14, 30], true)) {
            $hari = 14;
        }

        $totalUsers = User::count();
        $totalMenu = Menu::count();
        $totalPesanan = Pesanan::count();
        $pesananMenunggu = Pesanan::where('status', Pesanan::STATUS_MENUNGGU)->count();

        $pesananTerbaru = Pesanan::with(['customer', 'items.menu'])
            ->latest()
            ->take(5)
            ->get();

        $menuTersedia = Menu::where('ketersediaan', true)
            ->where('stok', '>', 0)
            ->latest()
            ->take(6)
            ->get();

        // --- Analitik lanjutan ---
        $tren = $this->analytics->trenPenjualan($hari);
        $perbandingan = $this->analytics->bandingkanPeriode(7);
        $jamSibuk = $this->analytics->jamSibuk(30);
        $menuTerlaris = $this->analytics->menuTerlaris(30, 5);
        $stokRendah = $this->analytics->stokRendah(10, 6);

        // Total pendapatan (lunas) 30 hari untuk kartu ringkasan.
        $pendapatan30 = (float) Pesanan::where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->whereNotIn('status', [Pesanan::STATUS_DIBATALKAN])
            ->where('tanggal', '>=', now()->subDays(29)->startOfDay())
            ->sum('total_harga');

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalMenu',
            'totalPesanan',
            'pesananMenunggu',
            'pesananTerbaru',
            'menuTersedia',
            'hari',
            'tren',
            'perbandingan',
            'jamSibuk',
            'menuTerlaris',
            'stokRendah',
            'pendapatan30',
        ));
    }
}
