<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =========================
        // ADMIN
        // =========================
        $adminUser = User::create([
            'name' => 'Admin Nongkrongin Univ',
            'email' => 'admin@nongkrongin.test',
            'password' => Hash::make('password'),
        ]);

        Admin::create([
            'user_id' => $adminUser->id,
            'id_admin' => 'ADM001',
            'nama' => 'Admin Nongkrongin Univ',
        ]);

        // =========================
        // KASIR
        // =========================
        $kasirUser = User::create([
            'name' => 'Kasir Nongkrongin Univ',
            'email' => 'kasir@nongkrongin.test',
            'password' => Hash::make('password'),
        ]);

        Kasir::create([
            'user_id' => $kasirUser->id,
            'id_kasir' => 'KSR001',
            'nama' => 'Kasir Nongkrongin Univ',
        ]);

        // =========================
        // CUSTOMER
        // =========================
        $customerUser = User::create([
            'name' => 'Customer Nongkrongin Univ',
            'email' => 'customer@nongkrongin.test',
            'password' => Hash::make('password'),
        ]);

        Customer::create([
            'user_id' => $customerUser->id,
            'nama' => 'Customer Nongkrongin Univ',
            'nim' => '20260001',
            'kelas' => 'TI-A',
        ]);
    }
}