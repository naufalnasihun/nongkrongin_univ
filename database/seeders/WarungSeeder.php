<?php

namespace Database\Seeders;

use App\Models\Kantin;
use App\Models\Warung;
use Illuminate\Database\Seeder;

class WarungSeeder extends Seeder
{
    public function run(): void
    {
        $kantinA = Kantin::where('nama_kantin', 'Kantin A')->first();
        $kantinB = Kantin::where('nama_kantin', 'Kantin B')->first();
        $kantinC = Kantin::where('nama_kantin', 'Kantin C')->first();

        $warungs = [
            // Kantin A - 2 warung
            ['kantin_id' => $kantinA->id, 'nama_warung' => 'Warung Bu Tini', 'pemilik' => 'Bu Tini', 'deskripsi' => 'Makanan berat harian.'],
            ['kantin_id' => $kantinA->id, 'nama_warung' => 'Warung Minuman Segar', 'pemilik' => 'Pak Surya', 'deskripsi' => 'Berbagai minuman dingin dan hangat.'],

            // Kantin B - 4 warung
            ['kantin_id' => $kantinB->id, 'nama_warung' => 'Warung Ayam Geprek Pak Joko', 'pemilik' => 'Pak Joko', 'deskripsi' => 'Ayam geprek dengan berbagai level sambal.'],
            ['kantin_id' => $kantinB->id, 'nama_warung' => 'Warung Mie & Nasi Goreng', 'pemilik' => 'Bu Siti', 'deskripsi' => 'Mie goreng, nasi goreng, dan berbagai olahan nasi.'],
            ['kantin_id' => $kantinB->id, 'nama_warung' => 'Warung Soto & Sop', 'pemilik' => 'Pak Budi', 'deskripsi' => 'Soto ayam, sop buntut, dan aneka sup hangat.'],
            ['kantin_id' => $kantinB->id, 'nama_warung' => 'Warung Jus & Es', 'pemilik' => 'Bu Rina', 'deskripsi' => 'Jus buah segar dan aneka es.'],

            // Kantin C - 6 warung
            ['kantin_id' => $kantinC->id, 'nama_warung' => 'Warung Nasi Padang', 'pemilik' => 'Pak Rusdi', 'deskripsi' => 'Masakan Padang dengan berbagai lauk.'],
            ['kantin_id' => $kantinC->id, 'nama_warung' => 'Warung Bakso & Mie Ayam', 'pemilik' => 'Pak Hadi', 'deskripsi' => 'Bakso sapi dan mie ayam.'],
            ['kantin_id' => $kantinC->id, 'nama_warung' => 'Warung Sehat', 'pemilik' => 'Bu Dewi', 'deskripsi' => 'Salad, sup, dan makanan sehat lainnya.'],
            ['kantin_id' => $kantinC->id, 'nama_warung' => 'Warung Roti & Snack', 'pemilik' => 'Bu Maya', 'deskripsi' => 'Roti bakar, gorengan, dan snack ringan.'],
            ['kantin_id' => $kantinC->id, 'nama_warung' => 'Warung Kopi & Teh', 'pemilik' => 'Pak Dedi', 'deskripsi' => 'Kopi, teh, dan minuman kekinian.'],
            ['kantin_id' => $kantinC->id, 'nama_warung' => 'Warung Khas Daerah', 'pemilik' => 'Pak Budi', 'deskripsi' => 'Masakan khas daerah dari berbagai wilayah.'],
        ];

        foreach ($warungs as $w) {
            Warung::firstOrCreate(
                ['kantin_id' => $w['kantin_id'], 'nama_warung' => $w['nama_warung']],
                $w
            );
        }
    }
}
