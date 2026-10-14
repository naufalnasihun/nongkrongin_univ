<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan snapshot nama & NIM customer pada saat pesanan dibuat.
 *
 * Tujuannya: riwayat pesanan dan struk tetap akurat walau customer
 * mengganti nama atau NIM-nya di kemudian hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->string('nama_customer_snapshot')->nullable()->after('customer_id');
            $table->string('nim_snapshot')->nullable()->after('nama_customer_snapshot');
        });

        $this->backfill();
    }

    /**
     * Isi snapshot dari data customer yang masih ada.
     *
     * Dilakukan per-baris (bukan UPDATE ... JOIN) supaya portabel
     * di MySQL maupun SQLite.
     */
    private function backfill(): void
    {
        $customers = DB::table('customers')->pluck('nama', 'id');
        $nims = DB::table('customers')->pluck('nim', 'id');

        DB::table('pesanans')
            ->select('id', 'customer_id')
            ->orderBy('id')
            ->chunkById(200, function ($pesanans) use ($customers, $nims) {
                foreach ($pesanans as $pesanan) {
                    if (! $pesanan->customer_id) {
                        continue;
                    }

                    DB::table('pesanans')
                        ->where('id', $pesanan->id)
                        ->update([
                            'nama_customer_snapshot' => $customers[$pesanan->customer_id] ?? null,
                            'nim_snapshot' => $nims[$pesanan->customer_id] ?? null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn(['nama_customer_snapshot', 'nim_snapshot']);
        });
    }
};
