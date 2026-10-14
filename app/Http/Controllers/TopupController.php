<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pengisian saldo (top-up) customer + konfirmasi oleh kasir.
 *
 * Alur: customer mengajukan top-up (mis. via QRIS) → status 'Menunggu'
 * → kasir memverifikasi & menyetujui → saldo customer bertambah.
 * Kolom `referensi` disiapkan untuk integrasi payment gateway QRIS
 * (placeholder, diisi manual pada tahap ini).
 */
class TopupController extends Controller
{
    /** Halaman saldo & riwayat top-up untuk customer. */
    public function index()
    {
        $customer = Auth::user()->customer;

        $topups = $customer->topups()
            ->latest()
            ->paginate(10);

        return view('customer.saldo', compact('customer', 'topups'));
    }

    /** Customer mengajukan permintaan top-up. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jumlah' => ['required', 'numeric', 'min:1000', 'max:2000000'],
            'metode' => ['required', 'in:qris,cash,transfer'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'jumlah.min' => 'Minimal top-up Rp 1.000.',
            'jumlah.max' => 'Maksimal top-up Rp 2.000.000.',
        ]);

        $customer = Auth::user()->customer;

        // Cegah pengajuan ganda yang masih menunggu.
        $menunggu = $customer->topups()
            ->where('status', Topup::STATUS_MENUNGGU)
            ->exists();

        if ($menunggu) {
            return back()->with('error', 'Anda masih memiliki pengajuan top-up yang belum dikonfirmasi.');
        }

        Topup::create([
            'customer_id' => $customer->id,
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'],
            'status' => Topup::STATUS_MENUNGGU,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan top-up dikirim. Silakan bayar & konfirmasi ke kasir.');
    }

    /** Kasir: daftar top-up yang menunggu konfirmasi. */
    public function kasirIndex()
    {
        $menunggu = Topup::with('customer')
            ->where('status', Topup::STATUS_MENUNGGU)
            ->oldest()
            ->get();

        $riwayat = Topup::with(['customer', 'dikonfirmasiOleh'])
            ->where('status', '!=', Topup::STATUS_MENUNGGU)
            ->latest('dikonfirmasi_pada')
            ->take(20)
            ->get();

        return view('kasir.topup', compact('menunggu', 'riwayat'));
    }

    /** Kasir: setujui top-up (saldo bertambah). */
    public function setujui(Request $request, Topup $topup)
    {
        if ($topup->status !== Topup::STATUS_MENUNGGU) {
            return back()->with('error', 'Top-up ini sudah diproses.');
        }

        DB::transaction(function () use ($topup) {
            $customer = $topup->customer()->lockForUpdate()->first();
            $customer->increment('saldo', $topup->jumlah);

            $topup->update([
                'status' => Topup::STATUS_DISETUJUI,
                'dikonfirmasi_oleh' => Auth::id(),
                'dikonfirmasi_pada' => now(),
                'referensi' => $topup->referensi ?: 'MANUAL-'.now()->format('YmdHis'),
            ]);
        });

        return back()->with('success', "Top-up Rp {$topup->jumlah} disetujui. Saldo customer bertambah.");
    }

    /** Kasir: tolak top-up. */
    public function tolak(Request $request, Topup $topup)
    {
        if ($topup->status !== Topup::STATUS_MENUNGGU) {
            return back()->with('error', 'Top-up ini sudah diproses.');
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $topup->update([
            'status' => Topup::STATUS_DITOLAK,
            'dikonfirmasi_oleh' => Auth::id(),
            'dikonfirmasi_pada' => now(),
            'catatan' => $validated['catatan'] ?? 'Ditolak oleh kasir.',
        ]);

        return back()->with('success', 'Top-up ditolak.');
    }
}
