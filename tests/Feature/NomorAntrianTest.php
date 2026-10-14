<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Kasir;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NomorAntrianTest extends TestCase
{
    use RefreshDatabase;

    private function buatCustomer(): User
    {
        $user = User::factory()->create();
        Customer::create(['user_id' => $user->id, 'nim' => '2495114101', 'nama' => 'Antri', 'kelas' => 'A']);

        return $user->fresh();
    }

    private function buatWarung(string $nama = 'Warung Antri'): Warung
    {
        $kantin = Kantin::create(['nama_kantin' => 'Kantin Antri']);

        return Warung::create(['kantin_id' => $kantin->id, 'nama_warung' => $nama]);
    }

    private function buatMenu(Warung $warung, string $nama = 'Menu Antri', int $stok = 20): Menu
    {
        return Menu::create([
            'warung_id' => $warung->id,
            'nama_menu' => $nama,
            'kategori' => 'makanan',
            'harga' => 10000,
            'stok' => $stok,
            'ketersediaan' => true,
        ]);
    }

    public function test_checkout_membuat_nomor_antrian_berurutan(): void
    {
        $customer = $this->buatCustomer();
        $warung = $this->buatWarung();
        $menu = $this->buatMenu($warung);

        // Pesanan pertama
        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.checkout'), ['metode_bayar' => 'cash']);

        // Pesanan kedua
        $this->actingAs($customer)->post(route('customer.cart.add', $menu), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.checkout'), ['metode_bayar' => 'cash']);

        $antrian = Pesanan::orderBy('id')->pluck('nomor_antrian')->all();

        $this->assertSame(['A-001', 'A-002'], $antrian);
        $this->assertSame($warung->id, Pesanan::first()->warung_id);
    }

    public function test_checkout_menolak_menu_dari_warung_berbeda(): void
    {
        $customer = $this->buatCustomer();
        $warungA = $this->buatWarung('Warung A');
        $warungB = $this->buatWarung('Warung B');
        $menuA = $this->buatMenu($warungA, 'Menu A');
        $menuB = $this->buatMenu($warungB, 'Menu B');

        $this->actingAs($customer)->post(route('customer.cart.add', $menuA), ['jumlah' => 1]);
        $this->actingAs($customer)->post(route('customer.cart.add', $menuB), ['jumlah' => 1]);

        $this->actingAs($customer)
            ->post(route('customer.checkout'), ['metode_bayar' => 'cash'])
            ->assertSessionHas('error');

        $this->assertSame(0, Pesanan::count(), 'Pesanan campur warung tidak boleh dibuat.');
    }

    public function test_pesanan_tidak_bisa_selesai_sebelum_lunas(): void
    {
        $customerUser = $this->buatCustomer();
        $customer = $customerUser->customer;

        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR-ANTRI', 'nama' => 'Kasir Antri']);
        $kasir = $kasirUser->fresh();

        $warung = $this->buatWarung();

        $pesanan = Pesanan::create([
            'customer_id' => $customer->id,
            'warung_id' => $warung->id,
            'nomor_pesanan' => 'NRG-ANTRI-0001',
            'nomor_antrian' => 'A-001',
            'tanggal' => now(),
            'total_harga' => 10000,
            'status' => Pesanan::STATUS_SIAP,
            'metode_bayar' => 'cash',
            'status_bayar' => Pesanan::BAYAR_BELUM,
        ]);

        // Coba selesaikan tanpa lunas -> ditolak.
        $this->actingAs($kasir)
            ->patch(route('kasir.orders.status', $pesanan), ['status' => Pesanan::STATUS_SELESAI])
            ->assertSessionHas('error');

        $this->assertSame(Pesanan::STATUS_SIAP, $pesanan->fresh()->status);

        // Tandai lunas.
        $this->actingAs($kasir)
            ->patch(route('kasir.orders.paid', $pesanan), ['metode_bayar' => 'qris'])
            ->assertSessionHas('success');

        $this->assertSame(Pesanan::BAYAR_LUNAS, $pesanan->fresh()->status_bayar);

        // Sekarang boleh selesai.
        $this->actingAs($kasir)
            ->patch(route('kasir.orders.status', $pesanan), ['status' => Pesanan::STATUS_SELESAI])
            ->assertSessionHas('success');

        $this->assertSame(Pesanan::STATUS_SELESAI, $pesanan->fresh()->status);
    }

    public function test_struk_bisa_dibuka_kasir_dan_customer(): void
    {
        $customerUser = $this->buatCustomer();
        $customer = $customerUser->customer;

        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR-STRUK', 'nama' => 'Kasir Struk']);
        $kasir = $kasirUser->fresh();

        $warung = $this->buatWarung();

        $pesanan = Pesanan::create([
            'customer_id' => $customer->id,
            'warung_id' => $warung->id,
            'nomor_pesanan' => 'NRG-STRUK-0001',
            'nomor_antrian' => 'B-007',
            'tanggal' => now(),
            'total_harga' => 15000,
            'status' => Pesanan::STATUS_MENUNGGU,
            'metode_bayar' => 'cash',
            'status_bayar' => Pesanan::BAYAR_BELUM,
        ]);

        PesananItem::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => null,
            'nama_menu_snapshot' => 'Menu Struk',
            'jumlah' => 1,
            'harga' => 15000,
            'harga_snapshot' => 15000,
            'subtotal' => 15000,
        ]);

        // Kasir lihat struk
        $this->actingAs($kasir)
            ->get(route('kasir.orders.struk', $pesanan))
            ->assertOk()
            ->assertSee('B-007')
            ->assertSee('Menu Struk');

        // Customer lihat struk pesanannya
        $this->actingAs($customerUser)
            ->get(route('customer.orders.struk', $pesanan))
            ->assertOk()
            ->assertSee('NRG-STRUK-0001');
    }

    public function test_customer_tidak_bisa_lihat_struk_customer_lain(): void
    {
        $customerUser = $this->buatCustomer();
        $customer = $customerUser->customer;

        $lainUser = User::factory()->create();
        $lain = Customer::create(['user_id' => $lainUser->id, 'nim' => '999', 'nama' => 'Lain', 'kelas' => 'Z']);

        $warung = $this->buatWarung();
        $pesanan = Pesanan::create([
            'customer_id' => $lain->id,
            'warung_id' => $warung->id,
            'nomor_pesanan' => 'NRG-LAIN-0001',
            'tanggal' => now(),
            'total_harga' => 10000,
            'status' => Pesanan::STATUS_MENUNGGU,
            'metode_bayar' => 'cash',
            'status_bayar' => Pesanan::BAYAR_BELUM,
        ]);

        $this->actingAs($customerUser)
            ->get(route('customer.orders.struk', $pesanan))
            ->assertForbidden();
    }
}
