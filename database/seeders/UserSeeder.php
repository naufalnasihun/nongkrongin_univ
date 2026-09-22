<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Kasir;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin Statis
        $adminUser = User::create([
            'name' => 'Admin Kantin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        Admin::create([
            'user_id' => $adminUser->id,
            'id_admin' => 'ADM001',
            'nama' => 'Admin Utama',
        ]);

        // 2. Akun Kasir Statis
        $kasirUser = User::create([
            'name' => 'Kasir Kantin',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('password'),
        ]);
        Kasir::create([
            'user_id' => $kasirUser->id,
            'id_kasir' => 'KSR001',
            'nama' => 'Kasir Utama',
        ]);

        // 3. Akun Customer Statis (Naufal Nashihun)
        $customerUser = User::create([
            'name' => 'Naufal Nashihun Nizhom',
            'email' => 'naufal@gmail.com',
            'password' => Hash::make('password'),
        ]);
        Customer::create([
            'user_id' => $customerUser->id,
            'nim' => '2495114016',
            'nama' => 'Naufal Nashihun Nizhom',
            'kelas' => 'Karyawan / V',
        ]);

        // 4. Buat 10 Customer Acak via Factory
        Customer::factory(10)->create();
    }
}