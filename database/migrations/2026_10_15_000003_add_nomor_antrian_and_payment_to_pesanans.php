<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            // Nomor antrian per-warung per-hari, contoh: A-012
            $table->string('nomor_antrian', 10)->nullable()->after('nomor_pesanan');
            // Warung tempat pesanan diproses (untuk scoping antrian & kasir).
            $table->foreignId('warung_id')->nullable()->after('customer_id')
                ->constrained('warungs')->nullOnDelete();
            // Metode & status pembayaran.
            $table->string('metode_bayar', 20)->default('cash')->after('status');
            $table->string('status_bayar', 20)->default('Belum Bayar')->after('metode_bayar');

            $table->index(['warung_id', 'tanggal', 'nomor_antrian']);
        });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropIndex(['warung_id', 'tanggal', 'nomor_antrian']);
            $table->dropForeign(['warung_id']);
            $table->dropColumn(['nomor_antrian', 'warung_id', 'metode_bayar', 'status_bayar']);
        });
    }
};
