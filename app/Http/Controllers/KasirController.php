<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePesananStatusRequest;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = $this->scopedQuery($request);

        $pesananMasuk = (clone $query)->where('status', Pesanan::STATUS_MENUNGGU)->count();
        $pesananDiproses = (clone $query)->where('status', Pesanan::STATUS_DIPROSES)->count();
        $pesananSiap = (clone $query)->where('status', Pesanan::STATUS_SIAP)->count();
        $pesananSelesaiHariIni = (clone $query)
            ->where('status', Pesanan::STATUS_SELESAI)
            ->whereDate('tanggal', today())
            ->count();

        $pesananTerbaru = (clone $query)
            ->with(['customer', 'items.menu.warung.kantin'])
            ->latest()
            ->take(10)
            ->get();

        $latestId = (clone $query)->max('id') ?? 0;

        // ---- Statistik harian kasir ----
        $hariIni = $this->statistikHarian($request);

        return view('kasir.dashboard', compact(
            'pesananMasuk',
            'pesananDiproses',
            'pesananSiap',
            'pesananSelesaiHariIni',
            'pesananTerbaru',
            'latestId',
            'hariIni'
        ));
    }

    /**
     * Ringkasan performa kasir untuk hari ini.
     *
     * @return array<string, mixed>
     */
    private function statistikHarian(Request $request): array
    {
        $base = fn () => $this->scopedQuery($request)->whereDate('tanggal', today());

        $totalPesanan = $base()->count();
        $lunas = $base()->where('status_bayar', Pesanan::BAYAR_LUNAS)->count();
        $belumBayar = $base()->where('status_bayar', '!=', Pesanan::BAYAR_LUNAS)
            ->where('status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->count();
        $dibatalkan = $base()->where('status', Pesanan::STATUS_DIBATALKAN)->count();

        $pendapatan = (float) $base()
            ->where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->where('status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->sum('total_harga');

        // Rincian per metode bayar (hari ini, hanya yang sudah lunas).
        $perMetode = $base()
            ->where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->selectRaw('metode_bayar, COUNT(*) as jumlah')
            ->selectRaw('SUM(total_harga) as total')
            ->groupBy('metode_bayar')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->metode_bayar => [
                    'jumlah' => (int) $r->jumlah,
                    'total' => (float) $r->total,
                ],
            ])
            ->all();

        // Distribusi per jam (pesanan masuk hari ini).
        // Dihitung di PHP agar portabel antara MySQL & SQLite (fungsi HOUR()
        // dan strftime() berbeda).
        $perJam = $base()
            ->pluck('tanggal')
            ->map(fn ($t) => (int) Carbon::parse($t)->format('G'))
            ->countBy()
            ->sortKeys()
            ->map(fn ($jumlah, $jam) => ['jam' => (int) $jam, 'jumlah' => (int) $jumlah])
            ->values()
            ->all();

        return [
            'totalPesanan' => $totalPesanan,
            'lunas' => $lunas,
            'belumBayar' => $belumBayar,
            'dibatalkan' => $dibatalkan,
            'pendapatan' => $pendapatan,
            'rataRata' => $lunas > 0 ? round($pendapatan / $lunas) : 0,
            'perMetode' => $perMetode,
            'perJam' => $perJam,
        ];
    }

    public function orders(Request $request)
    {
        $query = $this->scopedQuery($request)
            ->with(['customer', 'items.menu.warung.kantin'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('nama', 'like', "%{$search}%"));
            });
        }

        $pesanans = $query->paginate(15)->withQueryString();

        $latestId = Pesanan::max('id') ?? 0;

        return view('kasir.orders', compact('pesanans', 'latestId'));
    }

    public function show(Pesanan $pesanan)
    {
        $pesanan->load(['customer', 'kasir', 'items.menu.warung.kantin', 'logs.user']);

        return view('kasir.orders-show', compact('pesanan'));
    }

    public function updateStatus(UpdatePesananStatusRequest $request, Pesanan $pesanan)
    {
        $validated = $request->validated();
        $statusBaru = $validated['status'];
        $statusLama = $pesanan->status;

        if ($statusBaru === $statusLama) {
            return back()->with('error', "Status pesanan sudah '{$statusLama}'.");
        }

        // Validasi transisi status (state machine).
        if (! $pesanan->bolehTransisiKe($statusBaru)) {
            $boleh = $pesanan->statusBerikutnya();
            $pesan = empty($boleh)
                ? "Pesanan berstatus '{$statusLama}' dan tidak dapat diubah lagi."
                : "Dari '{$statusLama}' hanya boleh ke: ".implode(', ', $boleh).'.';

            return back()->with('error', 'Transisi status tidak valid. '.$pesan);
        }

        // Pesanan hanya boleh diserahkan bila sudah lunas.
        if ($statusBaru === Pesanan::STATUS_SELESAI && $pesanan->status_bayar !== Pesanan::BAYAR_LUNAS) {
            return back()->with('error', 'Pesanan belum lunas. Tandai pembayaran lunas terlebih dahulu.');
        }

        DB::transaction(function () use ($pesanan, $statusBaru, $statusLama, $validated) {
            $pesanan->update([
                'status' => $statusBaru,
                'kasir_id' => Auth::user()->kasir->id,
            ]);

            // Bila pesanan dibatalkan, kembalikan stok yang sudah terpotong.
            if ($statusBaru === Pesanan::STATUS_DIBATALKAN) {
                $this->kembalikanStok($pesanan);
            }

            PesananLog::create([
                'pesanan_id' => $pesanan->id,
                'user_id' => Auth::id(),
                'dari_status' => $statusLama,
                'ke_status' => $statusBaru,
                'catatan' => $validated['catatan'] ?? null,
            ]);
        });

        return back()->with('success', "Status pesanan {$pesanan->nomor_pesanan} diperbarui menjadi {$statusBaru}.");
    }

    /**
     * Tandai pesanan sudah lunas (validasi pembayaran oleh kasir).
     */
    public function markPaid(Request $request, Pesanan $pesanan)
    {
        $validated = $request->validate([
            'metode_bayar' => ['required', 'in:cash,qris,deposit'],
        ]);

        if ($pesanan->status_bayar === Pesanan::BAYAR_LUNAS) {
            return back()->with('error', 'Pesanan ini sudah lunas.');
        }

        if ($pesanan->isFinal()) {
            return back()->with('error', 'Pesanan sudah final, tidak dapat diubah pembayarannya.');
        }

        // Pembayaran via saldo deposit: cek & potong saldo customer.
        if ($validated['metode_bayar'] === 'deposit') {
            $customer = $pesanan->customer;

            if (! $customer) {
                return back()->with('error', 'Customer tidak ditemukan, tidak bisa bayar via deposit.');
            }

            if ((float) $customer->saldo < (float) $pesanan->total_harga) {
                return back()->with('error', 'Saldo deposit customer tidak cukup (saldo: Rp '.number_format((float) $customer->saldo, 0, ',', '.').').');
            }
        }

        DB::transaction(function () use ($pesanan, $validated) {
            // Potong saldo bila bayar via deposit.
            if ($validated['metode_bayar'] === 'deposit' && $pesanan->customer) {
                $customer = $pesanan->customer()->lockForUpdate()->first();
                $customer->decrement('saldo', $pesanan->total_harga);
            }

            $pesanan->update([
                'status_bayar' => Pesanan::BAYAR_LUNAS,
                'metode_bayar' => $validated['metode_bayar'],
                'kasir_id' => Auth::user()->kasir->id,
            ]);

            $catatan = 'Pembayaran lunas via '.Pesanan::METODE_BAYAR[$validated['metode_bayar']].'.';
            if ($validated['metode_bayar'] === 'deposit') {
                $catatan .= ' Saldo deposit terpotong Rp '.number_format($pesanan->total_harga, 0, ',', '.').'.';
            }

            PesananLog::create([
                'pesanan_id' => $pesanan->id,
                'user_id' => Auth::id(),
                'dari_status' => $pesanan->status,
                'ke_status' => $pesanan->status,
                'catatan' => $catatan,
            ]);
        });

        return back()->with('success', "Pembayaran {$pesanan->nomor_pesanan} ditandai lunas.");
    }

    /**
     * Query dasar pesanan. Bila kasir dibatasi ke warung tertentu, filter di sini.
     * Saat ini kasir melihat semua pesanan (belum ada relasi kasir-warung).
     */
    private function scopedQuery(Request $request)
    {
        return Pesanan::query();
    }

    /**
     * Tampilkan struk pesanan versi cetak (HTML, siap Ctrl+P).
     */
    public function struk(Pesanan $pesanan)
    {
        $pesanan->load(['customer', 'kasir', 'items', 'warung.kantin']);

        return view('kasir.struk', compact('pesanan'));
    }

    /**
     * Unduh struk pesanan dalam format PDF.
     */
    public function strukPdf(Pesanan $pesanan)
    {
        $pesanan->load(['customer', 'kasir', 'items', 'warung.kantin']);

        $pdf = Pdf::loadView('kasir.struk-pdf', [
            'pesanan' => $pesanan,
        ])->setPaper([0, 0, 226.77, 600], 'portrait'); // ~80mm lebar struk

        return $pdf->download("struk-{$pesanan->nomor_pesanan}.pdf");
    }

    /**
     * Kembalikan stok menu untuk pesanan yang dibatalkan.
     */
    private function kembalikanStok(Pesanan $pesanan): void
    {
        foreach ($pesanan->items as $item) {
            if (! $item->menu_id) {
                continue;
            }

            $menu = Menu::lockForUpdate()->find($item->menu_id);

            if (! $menu) {
                continue;
            }

            $menu->increment('stok', $item->jumlah);

            if (! $menu->ketersediaan && $menu->stok > 0) {
                $menu->update(['ketersediaan' => true]);
            }
        }
    }
}
