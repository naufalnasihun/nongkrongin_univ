<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom stok + kategori enum di menus
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedInteger('stok')->default(0)->after('harga');
            $table->enum('kategori', ['makanan', 'minuman', 'snack', 'dessert'])
                ->default('makanan')
                ->after('nama_menu');
        });

        // 2) Snapshot nama_menu & harga di pesanan_items (historis aman)
        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->string('nama_menu_snapshot')->nullable()->after('menu_id');
            $table->decimal('harga_snapshot', 12, 2)->nullable()->after('nama_menu_snapshot');
        });

        // 3) Tabel audit trail transisi status pesanan
        Schema::create('pesanan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dari_status')->nullable();
            $table->string('ke_status');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_logs');

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->dropColumn(['nama_menu_snapshot', 'harga_snapshot']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['stok', 'kategori']);
        });
    }
};
