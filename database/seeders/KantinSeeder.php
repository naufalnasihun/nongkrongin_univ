<?php

namespace Database\Seeders;

use App\Models\Kantin;
use Illuminate\Database\Seeder;

class KantinSeeder extends Seeder
{
    public function run(): void
    {
        $kantins = [
            [
                'nama_kantin' => 'Kantin A',
                'lokasi' => 'Dekat Gedung F',
                'deskripsi' => 'Kantin utama di area gedung F.',
            ],
            [
                'nama_kantin' => 'Kantin B',
                'lokasi' => 'Dekat Lapangan',
                'deskripsi' => 'Kantin di area lapangan olahraga.',
            ],
            [
                'nama_kantin' => 'Kantin C',
                'lokasi' => 'Dekat Gedung C',
                'deskripsi' => 'Kantin dengan pilihan menu terlengkap.',
            ],
        ];

        foreach ($kantins as $k) {
            Kantin::firstOrCreate(['nama_kantin' => $k['nama_kantin']], $k);
        }
    }
}
