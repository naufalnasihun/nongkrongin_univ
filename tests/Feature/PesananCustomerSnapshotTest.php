<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Snapshot nama & NIM customer pada pesanan.
 *
 * Pesanan harus menyimpan identitas customer pada saat dibuat,
 * sehingga riwayat/struk tetap akurat walau data customer diubah.
 */
class PesananCustomerSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function buatCustomer(string $nama = 'Budi', string $nim = '2495114001'): User
    {
        $user = User::factory()->create();
        Customer::create(['user_id' => $user->id, 'nim' => $nim, 'nama' => $nama, 'kelas' => 'A']);

        return $user->fresh();
    }

    private function buatMenu(int $stok = 10): Menu
    {
        $kantin = Kantin::create(['nama_kantin' => 'Kantin Snap '.random_int(1000, 9999)]);
        $warung = Warung::create([
            'kantin_id' => $kantin->id,
            'nama_warung' => 'Warung Snap '.random_int(1000, 9999),
        ]);

        return Menu::create([
            'warung_id' => $warung->id,
            'nama_menu' => 'Menu Snap',
            'kategori' => 'makanan',
            'harga' => 10000,
            'stok' => $stok,
            'ketersediaan' => true,
        ]);
    }

    public function test_checkout_menyimpan_snapshot_nama_dan_nim(): void
    {
        $customer = $this->buatCustomer('Budi Santoso', '2495114001');
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.checkout'), ['metode_bayar' => 'cash']);

        $pesanan = Pesanan::first();

        $this->assertSame('Budi Santoso', $pesanan->nama_customer_snapshot);
        $this->assertSame('2495114001', $pesanan->nim_snapshot);
        $this->assertSame('Budi Santoso', $pesanan->nama_customer);
        $this->assertSame('2495114001', $pesanan->nim_customer);
    }

    public function test_snapshot_tidak_berubah_walau_customer_ganti_nama_dan_nim(): void
    {
        $customer = $this->buatCustomer('Nama Lama', '1111111111');
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.checkout'), ['metode_bayar' => 'cash']);

        $pesanan = Pesanan::first();

        // Customer mengganti nama & NIM setelah pesanan dibuat.
        $customer->customer->update(['nama' => 'Nama Baru', 'nim' => '9999999999']);

        $pesanan->refresh()->load('customer');

        $this->assertSame('Nama Lama', $pesanan->nama_customer_snapshot, 'Snapshot nama tidak boleh berubah.');
        $this->assertSame('1111111111', $pesanan->nim_snapshot, 'Snapshot NIM tidak boleh berubah.');
        $this->assertSame('Nama Lama', $pesanan->nama_customer, 'Accessor harus memakai snapshot, bukan data live.');
        $this->assertSame('1111111111', $pesanan->nim_customer);
    }

    public function test_accessor_fallback_ke_relasi_bila_snapshot_kosong(): void
    {
        $customer = $this->buatCustomer('Fallback User', '7777777777');

        // Pesanan lama tanpa snapshot (mis. dibuat sebelum migrasi).
        $pesanan = Pesanan::create([
            'customer_id' => $customer->customer->id,
            'nomor_pesanan' => 'NRG-OLD-0001',
            'tanggal' => now(),
            'total_harga' => 5000,
            'status' => Pesanan::STATUS_MENUNGGU,
        ]);

        $this->assertNull($pesanan->nama_customer_snapshot);
        $this->assertSame('Fallback User', $pesanan->nama_customer, 'Harus fallback ke relasi customer.');
        $this->assertSame('7777777777', $pesanan->nim_customer);
    }

    public function test_struk_menampilkan_nama_dan_nim_dari_snapshot(): void
    {
        $customer = $this->buatCustomer('Siti Aminah', '2495114099');
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.checkout'), ['metode_bayar' => 'cash']);

        // Ubah data customer agar snapshot teruji benar-benar dipakai.
        $customer->customer->update(['nama' => 'Diubah', 'nim' => '0000000000']);

        $pesanan = Pesanan::first();

        $this->actingAs($customer)
            ->get(route('customer.orders.struk', $pesanan))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('2495114099')
            ->assertDontSee('Diubah');
    }
}
