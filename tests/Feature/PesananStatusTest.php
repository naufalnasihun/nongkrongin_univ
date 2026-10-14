<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Kasir;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\PesananLog;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananStatusTest extends TestCase
{
    use RefreshDatabase;

    private Pesanan $pesanan;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $customerUser = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'nim' => '2495114002',
            'nama' => 'Ani',
            'kelas' => 'B',
        ]);

        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR01', 'nama' => 'Kasir Satu']);
        $this->kasir = $kasirUser->fresh();

        $warung = Warung::create([
            'kantin_id' => Kantin::create(['nama_kantin' => 'Kantin Status'])->id,
            'nama_warung' => 'Warung Status',
        ]);
        $menu = Menu::create([
            'warung_id' => $warung->id,
            'nama_menu' => 'Mie Status',
            'kategori' => 'makanan',
            'harga' => 10000,
            'stok' => 5,
            'ketersediaan' => true,
        ]);

        $this->pesanan = Pesanan::create([
            'customer_id' => $customer->id,
            'nomor_pesanan' => 'NRG-TEST-0001',
            'tanggal' => now(),
            'total_harga' => 20000,
            'status' => Pesanan::STATUS_MENUNGGU,
        ]);

        PesananItem::create([
            'pesanan_id' => $this->pesanan->id,
            'menu_id' => $menu->id,
            'nama_menu_snapshot' => 'Mie Status',
            'jumlah' => 2,
            'harga' => 10000,
            'harga_snapshot' => 10000,
            'subtotal' => 20000,
        ]);
    }

    public function test_transisi_valid_menunggu_ke_diproses(): void
    {
        $this->actingAs($this->kasir)
            ->patch(route('kasir.orders.status', $this->pesanan), ['status' => 'Diproses'])
            ->assertSessionHas('success');

        $this->assertSame('Diproses', $this->pesanan->fresh()->status);
        $this->assertSame(1, PesananLog::count());
    }

    public function test_transisi_ilegal_menunggu_ke_selesai_ditolak(): void
    {
        $this->actingAs($this->kasir)
            ->patch(route('kasir.orders.status', $this->pesanan), ['status' => 'Selesai'])
            ->assertSessionHas('error');

        $this->assertSame('Menunggu', $this->pesanan->fresh()->status, 'Status tidak boleh berubah.');
        $this->assertSame(0, PesananLog::count());
    }

    public function test_pesanan_selesai_tidak_bisa_diubah_lagi(): void
    {
        $this->pesanan->update(['status' => Pesanan::STATUS_SELESAI]);

        $this->actingAs($this->kasir)
            ->patch(route('kasir.orders.status', $this->pesanan), ['status' => 'Menunggu'])
            ->assertSessionHas('error');

        $this->assertSame('Selesai', $this->pesanan->fresh()->status);
    }

    public function test_pembatalan_mengembalikan_stok(): void
    {
        $menu = Menu::first();
        $menu->update(['stok' => 3]); // stok setelah 2 terpotong

        $this->actingAs($this->kasir)
            ->patch(route('kasir.orders.status', $this->pesanan), ['status' => 'Dibatalkan'])
            ->assertSessionHas('success');

        $this->assertSame('Dibatalkan', $this->pesanan->fresh()->status);
        $this->assertSame(5, $menu->fresh()->stok, 'Stok harus dikembalikan +2.');
    }
}
