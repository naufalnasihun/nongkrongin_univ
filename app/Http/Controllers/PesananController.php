<?php

namespace App\Http\Controllers;

use App\Models\Kantin;
use App\Models\Keranjang;
use App\Models\Menu;
use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function adminIndex(Request $request)
    {
        // Eager-load warung & kantin agar kolom "Lokasi" tidak N+1.
        $query = Pesanan::with(['customer', 'warung.kantin', 'items.menu.warung.kantin'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            // Dikelompokkan dalam closure supaya tidak "bocor" ke filter lain
            // (tanpa grouping, orWhere akan membatalkan kondisi status).
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_bayar')) {
            $query->where('status_bayar', $request->status_bayar);
        }

        $pesanans = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('pesanans'));
    }

    public function customerDashboard(Request $request)
    {
        $customer = Auth::user()->customer;

        $query = Menu::with('warung.kantin')->tersedia();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_menu', 'like', "%{$search}%");
        }

        if ($request->filled('kantin_id')) {
            $query->whereHas('warung', function ($q) use ($request) {
                $q->where('kantin_id', $request->kantin_id);
            });
        }

        if ($request->filled('warung_id')) {
            $query->where('warung_id', $request->warung_id);
        }

        // Filter kategori berbasis kolom enum (bukan lagi string-match nama menu).
        if ($request->filled('kategori')) {
            $query->kategori($request->kategori);
        }

        // Filter rentang harga.
        if ($request->filled('harga_min')) {
            $query->where('harga', '>=', (int) $request->harga_min);
        }

        if ($request->filled('harga_max')) {
            $query->where('harga', '<=', (int) $request->harga_max);
        }

        // Urutan.
        $sort = $request->input('sort');
        match ($sort) {
            'termurah' => $query->orderBy('harga'),
            'termahal' => $query->orderByDesc('harga'),
            default => $query->latest(),
        };

        $menus = $query->paginate(12)->withQueryString();

        $keranjang = Keranjang::where('customer_id', $customer->id)
            ->with('items.menu')
            ->first();

        $items = $keranjang?->items ?? collect();
        $totalKeranjang = $items->sum('subtotal');
        $jumlahItem = $items->sum('jumlah');

        $kantins = Kantin::with('warungs')->orderBy('nama_kantin')->get();

        return view('customer.dashboard', compact(
            'menus',
            'items',
            'totalKeranjang',
            'jumlahItem',
            'customer',
            'kantins'
        ));
    }

    public function customerOrders(Request $request)
    {
        $customerId = Auth::user()->customer->id;

        $query = Pesanan::where('customer_id', $customerId)
            ->with('items.menu.warung.kantin');

        // Filter status pesanan.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter status pembayaran.
        if ($request->filled('bayar')) {
            $query->where('status_bayar', $request->bayar);
        }

        // Pencarian nomor pesanan.
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nomor_pesanan', 'like', "%{$search}%");
        }

        // Rentang tanggal.
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $pesanans = $query->latest()->paginate(10)->withQueryString();

        $statuses = [
            Pesanan::STATUS_MENUNGGU,
            Pesanan::STATUS_DIPROSES,
            Pesanan::STATUS_SIAP,
            Pesanan::STATUS_SELESAI,
            Pesanan::STATUS_DIBATALKAN,
        ];

        return view('customer.orders', compact('pesanans', 'statuses'));
    }

    /**
     * Struk versi cetak untuk customer (hanya pesanan miliknya).
     */
    public function struk(Pesanan $pesanan)
    {
        $this->pastikanMilikCustomer($pesanan);

        $pesanan->load(['customer', 'kasir', 'items', 'warung.kantin']);

        return view('kasir.struk', compact('pesanan'));
    }

    /**
     * Struk PDF untuk customer (hanya pesanan miliknya).
     */
    public function strukPdf(Pesanan $pesanan)
    {
        $this->pastikanMilikCustomer($pesanan);

        $pesanan->load(['customer', 'kasir', 'items', 'warung.kantin']);

        $pdf = Pdf::loadView('kasir.struk-pdf', [
            'pesanan' => $pesanan,
        ])->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->download("struk-{$pesanan->nomor_pesanan}.pdf");
    }

    private function pastikanMilikCustomer(Pesanan $pesanan): void
    {
        if ($pesanan->customer_id !== Auth::user()->customer->id) {
            abort(403, 'Anda tidak berhak melihat pesanan ini.');
        }
    }
}
