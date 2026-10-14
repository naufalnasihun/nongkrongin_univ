<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('warung_id')
                ->nullable()
                ->after('id')
                ->constrained('warungs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['warung_id']);
            $table->dropColumn('warung_id');
        });
    }
};
