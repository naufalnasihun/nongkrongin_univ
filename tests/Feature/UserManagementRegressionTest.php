<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        Admin::create(['user_id' => $user->id, 'id_admin' => 'ADM01', 'nama' => 'Admin Utama']);

        return $user->fresh();
    }

    /**
     * REGRESI: dulu update user menghapus profil customer (delete-recreate),
     * yang lewat cascade FK ikut menghapus seluruh pesanan customer tersebut.
     */
    public function test_update_user_tidak_menghapus_riwayat_pesanan_customer(): void
    {
        $admin = $this->buatAdmin();

        $customerUser = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama@example.com']);
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'nim' => '2495114099',
            'nama' => 'Customer Lama',
            'kelas' => 'Z',
        ]);

        $pesanan = Pesanan::create([
            'customer_id' => $customer->id,
            'nomor_pesanan' => 'NRG-REG-0001',
            'tanggal' => now(),
            'total_harga' => 25000,
            'status' => Pesanan::STATUS_MENUNGGU,
        ]);

        PesananItem::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => null,
            'nama_menu_snapshot' => 'Menu Lama',
            'jumlah' => 1,
            'harga' => 25000,
            'harga_snapshot' => 25000,
            'subtotal' => 25000,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $customerUser), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'password' => '',
            'role' => 'Customer',
            'nama' => 'Customer Baru',
            'nim' => '2495114099',
            'kelas' => 'Y',
        ])->assertRedirect(route('admin.users.index'));

        // Pesanan HARUS tetap ada.
        $this->assertSame(1, Pesanan::count(), 'Riwayat pesanan customer tidak boleh terhapus!');
        $this->assertSame(1, PesananItem::count(), 'Item pesanan tidak boleh terhapus!');

        // Profil customer ter-update in-place (id sama, tidak dibuat ulang).
        $this->assertSame(1, Customer::count());
        $this->assertSame($customer->id, $customerUser->fresh()->customer->id);
        $this->assertSame('Customer Baru', $customerUser->fresh()->customer->nama);
    }

    public function test_role_tidak_bisa_diubah_bila_sudah_punya_pesanan(): void
    {
        $admin = $this->buatAdmin();

        $customerUser = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'nim' => '2495114088',
            'nama' => 'Cust',
            'kelas' => 'A',
        ]);

        Pesanan::create([
            'customer_id' => $customer->id,
            'nomor_pesanan' => 'NRG-REG-0002',
            'tanggal' => now(),
            'total_harga' => 10000,
            'status' => Pesanan::STATUS_MENUNGGU,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $customerUser), [
            'name' => 'Ubah Role',
            'email' => $customerUser->email,
            'role' => 'Kasir',
            'nama' => 'Kasir Baru',
            'id_kasir' => 'KSR99',
        ])->assertSessionHasErrors('role');

        $this->assertSame('Customer', $customerUser->fresh()->customer ? 'Customer' : 'Kasir');
    }

    public function test_admin_tidak_bisa_hapus_dirinya_sendiri(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertSame(1, User::count());
    }
}
