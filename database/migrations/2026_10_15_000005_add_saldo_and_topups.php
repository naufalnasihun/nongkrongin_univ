<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saldo deposit customer + riwayat top-up.
 *
 * Kolom `saldo` menyimpan saldo aktif customer. Tabel `topups` mencatat
 * setiap permintaan pengisian saldo (diajukan customer, dikonfirmasi kasir).
 * Fitur pembayaran QRIS gateway tetap placeholder: `referensi` disediakan
 * untuk menampung ID transaksi dari penyedia QRIS di masa depan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('saldo', 12, 2)->default(0)->after('kelas');
        });

        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('metode')->default('qris'); // qris | cash | transfer
            $table->string('status')->default('Menunggu'); // Menunggu | Disetujui | Ditolak
            $table->string('referensi')->nullable(); // ID transaksi QRIS (placeholder)
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topups');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
    }
};
