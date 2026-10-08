<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::create([
            'nama_menu' => 'Nasi Goreng',
            'foto' => 'menu/nasi-goreng.jpg',
            'harga' => 15000,
            'deskripsi' => 'Nasi goreng dengan telur dan sayuran.',
            'ketersediaan' => true,
        ]);

        Menu::create([
            'nama_menu' => 'Mie Goreng',
            'foto' => 'menu/mie-goreng.jpg',
            'harga' => 12000,
            'deskripsi' => 'Mie goreng dengan telur dan sayuran.',
            'ketersediaan' => true,
        ]);

        Menu::create([
            'nama_menu' => 'Ayam Geprek',
            'foto' => 'menu/ayam-geprek.jpg',
            'harga' => 18000,
            'deskripsi' => 'Ayam crispy dengan sambal geprek.',
            'ketersediaan' => true,
        ]);

        Menu::create([
            'nama_menu' => 'Es Teh',
            'foto' => 'menu/es-teh.jpg',
            'harga' => 5000,
            'deskripsi' => 'Minuman teh manis dingin.',
            'ketersediaan' => true,
        ]);

        Menu::create([
            'nama_menu' => 'Es Jeruk',
            'foto' => 'menu/es-jeruk.jpg',
            'harga' => 7000,
            'deskripsi' => 'Minuman jeruk segar.',
            'ketersediaan' => true,
        ]);
    }
}