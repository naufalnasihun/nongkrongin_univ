<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warungs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kantin_id')
                ->constrained('kantins')
                ->cascadeOnDelete();

            $table->string('nama_warung');
            $table->string('pemilik')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warungs');
    }
};
