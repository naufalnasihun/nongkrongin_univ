<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah FK pesanan_items.menu_id dari cascadeOnDelete -> nullOnDelete dan
     * buat menu_id nullable. Tujuannya: menghapus menu tidak lagi menghapus
     * line item pada riwayat pesanan lama (snapshot nama & harga tetap utuh).
     */
    public function up(): void
    {
        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->nullable()->change();
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->foreign('menu_id')
                ->references('id')
                ->on('menus')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->nullable(false)->change();
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->foreign('menu_id')
                ->references('id')
                ->on('menus')
                ->cascadeOnDelete();
        });
    }
};
