<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function buatCustomer(): User
    {
        $user = User::factory()->create();
        Customer::create(['user_id' => $user->id, 'nim' => '2495114001', 'nama' => 'Budi', 'kelas' => 'A']);

        return $user->fresh();
    }

    private function buatMenu(int $stok = 10, int $harga = 15000): Menu
    {
        $kantin = Kantin::create(['nama_kantin' => 'Kantin Test']);
        $warung = Warung::create([
            'kantin_id' => $kantin->id,
            'nama_warung' => 'Warung Test',
            'pemilik' => 'Pak Test',
        ]);

        return Menu::create([
            'warung_id' => $warung->id,
            'nama_menu' => 'Nasi Test',
            'kategori' => 'makanan',
            'harga' => $harga,
            'stok' => $stok,
            'ketersediaan' => true,
        ]);
    }

    public function test_checkout_mengurangi_stok_dan_membuat_snapshot(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu(stok: 10, harga: 15000);

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 3]);

        $this->actingAs($customer)->post(route('customer.checkout'), ['metode_bayar' => 'cash'])->assertRedirect(route('customer.orders.index'));

        $this->assertSame(7, $menu->fresh()->stok, 'Stok harus berkurang 3.');

        $item = PesananItem::first();
        $this->assertSame('Nasi Test', $item->nama_menu_snapshot);
        $this->assertEquals(15000, $item->harga_snapshot);
        $this->assertSame(3, $item->jumlah);

        $pesanan = Pesanan::first();
        $this->assertEquals(45000, $pesanan->total_harga);
        $this->assertSame(Pesanan::STATUS_MENUNGGU, $pesanan->status);
    }

    public function test_checkout_ditolak_bila_stok_tidak_cukup(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu(stok: 10);

        // Tambah 10 (maksimal), lalu stok diturunkan oleh admin menjadi 2.
        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 10]);
        $menu->update(['stok' => 2]);

        $this->actingAs($customer)
            ->post(route('customer.checkout'), ['metode_bayar' => 'cash'])
            ->assertSessionHas('error');

        $this->assertSame(0, Pesanan::count(), 'Tidak boleh ada pesanan dibuat.');
        $this->assertSame(2, $menu->fresh()->stok, 'Stok tidak boleh berubah.');
    }

    public function test_tidak_bisa_menambah_melebihi_stok(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu(stok: 2);

        $this->actingAs($customer)
            ->post(route('customer.cart.add', $menu), ['jumlah' => 5])
            ->assertSessionHas('error');

        $this->assertSame(0, (int) \DB::table('keranjang_items')->sum('jumlah'));
    }

    public function test_menu_habis_tidak_muncul_di_dashboard_customer(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu(stok: 0);
        $menu->update(['ketersediaan' => false]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertDontSee('Nasi Test');
    }

    public function test_checkout_menyimpan_metode_pembayaran_qris(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.checkout'), [
            'metode_bayar' => 'qris',
            'catatan' => 'Tanpa sambal',
        ])->assertRedirect(route('customer.orders.index'));

        $pesanan = Pesanan::first();
        $this->assertSame('qris', $pesanan->metode_bayar);
        $this->assertSame(Pesanan::BAYAR_BELUM, $pesanan->status_bayar, 'Pembayaran belum boleh langsung lunas.');
        $this->assertStringContainsString('Tanpa sambal', $pesanan->logs()->first()->catatan);
    }

    public function test_checkout_ditolak_tanpa_metode_pembayaran(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);

        $this->actingAs($customer)
            ->post(route('customer.checkout'))
            ->assertSessionHasErrors('metode_bayar');

        $this->assertSame(0, Pesanan::count(), 'Tidak boleh ada pesanan tanpa metode bayar.');
    }

    public function test_checkout_ditolak_metode_pembayaran_tidak_dikenal(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);

        $this->actingAs($customer)
            ->post(route('customer.checkout'), ['metode_bayar' => 'bitcoin'])
            ->assertSessionHasErrors('metode_bayar');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_dashboard_customer_menampilkan_pilihan_metode_bayar(): void
    {
        $customer = $this->buatCustomer();
        $menu = $this->buatMenu();

        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Metode Pembayaran')
            ->assertSee('Tunai')
            ->assertSee('QRIS')
            ->assertSee('Deposit');
    }
}
