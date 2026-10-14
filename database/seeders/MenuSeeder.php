<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Warung;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $warungMie = Warung::where('nama_warung', 'Warung Mie & Nasi Goreng')->first();
        $warungGeprek = Warung::where('nama_warung', 'Warung Ayam Geprek Pak Joko')->first();
        $warungMinuman = Warung::where('nama_warung', 'Warung Minuman Segar')->first();

        if (! $warungMie || ! $warungGeprek || ! $warungMinuman) {
            $this->command?->warn('WarungSeeder belum dijalankan. MenuSeeder dilewati.');

            return;
        }

        $menus = [
            [
                'warung_id' => $warungMie->id,
                'nama_menu' => 'Nasi Goreng',
                'kategori' => 'makanan',
                'foto' => 'menu/nasi-goreng.jpg',
                'harga' => 15000,
                'stok' => 20,
                'deskripsi' => 'Nasi goreng dengan telur dan sayuran.',
                'ketersediaan' => true,
            ],
            [
                'warung_id' => $warungMie->id,
                'nama_menu' => 'Mie Goreng',
                'kategori' => 'makanan',
                'foto' => 'menu/mie-goreng.jpg',
                'harga' => 12000,
                'stok' => 20,
                'deskripsi' => 'Mie goreng dengan telur dan sayuran.',
                'ketersediaan' => true,
            ],
            [
                'warung_id' => $warungGeprek->id,
                'nama_menu' => 'Ayam Geprek',
                'kategori' => 'makanan',
                'foto' => 'menu/ayam-geprek.jpg',
                'harga' => 18000,
                'stok' => 15,
                'deskripsi' => 'Ayam crispy dengan sambal geprek.',
                'ketersediaan' => true,
            ],
            [
                'warung_id' => $warungGeprek->id,
                'nama_menu' => 'Kerupuk Seblak',
                'kategori' => 'snack',
                'foto' => null,
                'harga' => 5000,
                'stok' => 30,
                'deskripsi' => 'Kerupuk seblak pedas gurih.',
                'ketersediaan' => true,
            ],
            [
                'warung_id' => $warungMinuman->id,
                'nama_menu' => 'Es Teh',
                'kategori' => 'minuman',
                'foto' => 'menu/es-teh.jpg',
                'harga' => 5000,
                'stok' => 50,
                'deskripsi' => 'Minuman teh manis dingin.',
                'ketersediaan' => true,
            ],
            [
                'warung_id' => $warungMinuman->id,
                'nama_menu' => 'Es Jeruk',
                'kategori' => 'minuman',
                'foto' => 'menu/es-jeruk.jpg',
                'harga' => 7000,
                'stok' => 40,
                'deskripsi' => 'Minuman jeruk segar.',
                'ketersediaan' => true,
            ],
            [
                'warung_id' => $warungMinuman->id,
                'nama_menu' => 'Puding Coklat',
                'kategori' => 'dessert',
                'foto' => null,
                'harga' => 8000,
                'stok' => 10,
                'deskripsi' => 'Puding coklat lembut.',
                'ketersediaan' => true,
            ],
        ];

        foreach ($menus as $m) {
            Menu::updateOrCreate(
                ['nama_menu' => $m['nama_menu'], 'warung_id' => $m['warung_id']],
                $m
            );
        }
    }
}
