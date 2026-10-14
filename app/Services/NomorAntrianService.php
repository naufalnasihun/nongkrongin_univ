<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Warung;
use Illuminate\Support\Facades\DB;

class NomorAntrianService
{
    /**
     * Buat nomor antrian untuk sebuah warung pada tanggal tertentu.
     *
     * Format: "{HURUF}-{URUT:3}" contoh "A-001".
     * Huruf diambil dari urutan warung di dalam kantinnya (A, B, C, ...),
     * urut direset setiap hari.
     *
     * WAJIB dipanggil di dalam DB::transaction agar lock efektif.
     */
    public function generate(Warung $warung, ?string $tanggal = null): string
    {
        $tanggal = $tanggal ?? now()->toDateString();
        $huruf = $this->hurufWarung($warung);

        // Lock baris pesanan warung pada hari ini untuk mencegah race.
        $atas = Pesanan::where('warung_id', $warung->id)
            ->whereDate('tanggal', $tanggal)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('nomor_antrian');

        $urut = 1;
        if ($atas && preg_match('/-(\d+)$/', $atas, $m)) {
            $urut = (int) $m[1] + 1;
        }

        return $huruf.'-'.str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Tentukan huruf warung berdasarkan posisinya di dalam satu kantin.
     */
    private function hurufWarung(Warung $warung): string
    {
        $index = Warung::where('kantin_id', $warung->kantin_id)
            ->orderBy('id')
            ->pluck('id')
            ->search($warung->id);

        $index = $index === false ? 0 : (int) $index;

        // A..Z, lalu lanjut AA, AB, ... bila lebih dari 26 warung.
        return $this->kolomKeHuruf($index);
    }

    private function kolomKeHuruf(int $index): string
    {
        $huruf = '';
        $index++;

        while ($index > 0) {
            $index--;
            $huruf = chr(65 + ($index % 26)).$huruf;
            $index = intdiv($index, 26);
        }

        return $huruf;
    }
}
