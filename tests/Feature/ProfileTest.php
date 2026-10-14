<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function buatCustomer(string $nama = 'Budi', string $nim = '2495114001'): User
    {
        $user = User::factory()->create(['name' => 'budi', 'email' => 'u'.random_int(100000, 999999).'@test.local']);
        Customer::create(['user_id' => $user->id, 'nim' => $nim, 'nama' => $nama, 'kelas' => 'A']);

        return $user->fresh();
    }

    public function test_customer_bisa_lihat_halaman_profil(): void
    {
        $this->actingAs($this->buatCustomer())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Data Profil')
            ->assertSee('Ganti Password')
            ->assertSee('2495114001');
    }

    public function test_semua_role_bisa_akses_profil(): void
    {
        $adminUser = User::factory()->create();
        Admin::create(['user_id' => $adminUser->id, 'id_admin' => 'ADM-P', 'nama' => 'Admin P']);

        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR-P', 'nama' => 'Kasir P']);

        $this->actingAs($adminUser->fresh())->get(route('profile.edit'))->assertOk();
        $this->actingAs($kasirUser->fresh())->get(route('profile.edit'))->assertOk();
        $this->actingAs($this->buatCustomer())->get(route('profile.edit'))->assertOk();
    }

    public function test_customer_bisa_update_nama_dan_nim(): void
    {
        $customer = $this->buatCustomer('Nama Lama', '1111111111');

        $this->actingAs($customer)
            ->patch(route('profile.update'), [
                'name' => 'budi baru',
                'email' => $customer->email,
                'nama' => 'Budi Baru',
                'nim' => '2222222222',
                'kelas' => 'TI-4A',
            ])
            ->assertSessionHas('success');

        $customer->refresh()->load('customer');
        $this->assertSame('budi baru', $customer->name);
        $this->assertSame('Budi Baru', $customer->customer->nama);
        $this->assertSame('2222222222', $customer->customer->nim);
        $this->assertSame('TI-4A', $customer->customer->kelas);
    }

    public function test_nim_harus_unik(): void
    {
        $this->buatCustomer('Orang Lain', '9999999999');
        $customer = $this->buatCustomer('Budi', '1111111111');

        $this->actingAs($customer)
            ->patch(route('profile.update'), [
                'name' => 'budi',
                'email' => $customer->email,
                'nama' => 'Budi',
                'nim' => '9999999999',
            ])
            ->assertSessionHasErrors('nim');
    }

    public function test_email_harus_unik(): void
    {
        User::factory()->create(['email' => 'dipakai@test.local']);
        $customer = $this->buatCustomer();

        $this->actingAs($customer)
            ->patch(route('profile.update'), [
                'name' => 'budi',
                'email' => 'dipakai@test.local',
                'nama' => 'Budi',
                'nim' => '2495114001',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_user_tidak_bisa_ganti_password_dengan_password_salah(): void
    {
        $customer = $this->buatCustomer();

        $this->actingAs($customer)
            ->patch(route('profile.password'), [
                'current_password' => 'salah-banget',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_user_bisa_ganti_password(): void
    {
        $customer = $this->buatCustomer();

        $this->actingAs($customer)
            ->patch(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordbaru123', $customer->fresh()->password));
    }

    public function test_konfirmasi_password_harus_cocok(): void
    {
        $this->actingAs($this->buatCustomer())
            ->patch(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'beda12345678',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_password_minimal_8_karakter(): void
    {
        $this->actingAs($this->buatCustomer())
            ->patch(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_tamu_tidak_bisa_akses_profil(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
