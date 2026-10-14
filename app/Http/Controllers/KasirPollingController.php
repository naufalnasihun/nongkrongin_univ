<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KasirPollingController extends Controller
{
    /**
     * Endpoint polling untuk notifikasi realtime di panel kasir.
     *
     * Mengembalikan ringkasan jumlah pesanan per status + daftar pesanan
     * yang baru masuk sejak `since_id` supaya UI bisa memunculkan toast.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = Pesanan::query();

        $masuk = (clone $query)->where('status', Pesanan::STATUS_MENUNGGU)->count();
        $diproses = (clone $query)->where('status', Pesanan::STATUS_DIPROSES)->count();
        $siap = (clone $query)->where('status', Pesanan::STATUS_SIAP)->count();
        $belumBayar = (clone $query)
            ->where('status_bayar', Pesanan::BAYAR_BELUM)
            ->where('status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->count();

        $sinceId = (int) $request->input('since_id', 0);

        $baru = collect();
        if ($sinceId >= 0) {
            $baru = Pesanan::with('customer:id,nama,nim')
                ->where('id', '>', $sinceId)
                ->where('status', Pesanan::STATUS_MENUNGGU)
                ->orderBy('id')
                ->take(10)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'nomor_pesanan' => $p->nomor_pesanan,
                    'nomor_antrian' => $p->nomor_antrian,
                    'customer' => $p->nama_customer,
                    'nim' => $p->nim_customer,
                    'total' => (float) $p->total_harga,
                    'waktu' => $p->tanggal?->format('H:i'),
                ]);
        }

        $terbaru = (clone $query)->max('id') ?? 0;

        return response()->json([
            'counts' => [
                'masuk' => $masuk,
                'diproses' => $diproses,
                'siap' => $siap,
                'belum_bayar' => $belumBayar,
                'total_aktif' => $masuk + $diproses + $siap,
            ],
            'baru' => $baru,
            'latest_id' => (int) $terbaru,
            'server_time' => now()->format('H:i:s'),
        ]);
    }
}
