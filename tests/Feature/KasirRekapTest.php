<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Kasir;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rekap harian kasir (tutup buku).
 */
class KasirRekapTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    private Customer $customer;

    private Warung $warung;

    protected function setUp(): void
    {
        parent::setUp();

        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR-REK', 'nama' => 'Kasir Rekap']);
        $this->kasir = $kasirUser->fresh();

        $customerUser = User::factory()->create();
        $this->customer = Customer::create([
            'user_id' => $customerUser->id,
            'nim' => '77778888',
            'nama' => 'Customer Rekap',
            'kelas' => 'A',
        ]);

        $this->warung = Warung::create([
            'kantin_id' => Kantin::create(['nama_kantin' => 'Kantin Rekap'])->id,
            'nama_warung' => 'Warung Rekap',
        ]);
    }

    private function buatPesanan(string $nomor, string $statusBayar, string $status, int $total = 20000, string $metode = 'cash', string $tanggal = 'now'): Pesanan
    {
        return Pesanan::create([
            'customer_id' => $this->customer->id,
            'warung_id' => $this->warung->id,
            'nomor_pesanan' => $nomor,
            'status' => $status,
            'status_bayar' => $statusBayar,
            'metode_bayar' => $metode,
            'total_harga' => $total,
            'tanggal' => $tanggal === 'now' ? now() : $tanggal,
        ]);
    }

    public function test_kasir_bisa_akses_halaman_rekap(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('kasir.rekap'))
            ->assertOk()
            ->assertSee('Rekap Harian');
    }

    public function test_rekap_menghitung_pendapatan_hanya_lunas(): void
    {
        $this->buatPesanan('ORD-LUNAS', 'Lunas', Pesanan::STATUS_SELESAI, 25000, 'cash');
        $this->buatPesanan('ORD-BELUM', 'Belum Bayar', Pesanan::STATUS_MENUNGGU, 30000, 'qris');

        $this->actingAs($this->kasir)
            ->get(route('kasir.rekap'))
            ->assertOk()
            ->assertSee('ORD-LUNAS')
            ->assertSee('25.000')
            // Pesanan belum bayar tidak muncul di tabel verifikasi kas.
            ->assertDontSee('ORD-BELUM');
    }

    public function test_rekap_menampilkan_rincian_metode_bayar(): void
    {
        $this->buatPesanan('ORD-QRIS-1', 'Lunas', Pesanan::STATUS_SELESAI, 15000, 'qris');
        $this->buatPesanan('ORD-CASH-1', 'Lunas', Pesanan::STATUS_SELESAI, 10000, 'cash');

        $this->actingAs($this->kasir)
            ->get(route('kasir.rekap'))
            ->assertOk()
            ->assertSee('Per Metode Bayar')
            ->assertSee('QRIS')
            ->assertSee('Tunai');
    }

    public function test_rekap_menampilkan_menu_terjual(): void
    {
        $pesanan = $this->buatPesanan('ORD-MENU', 'Lunas', Pesanan::STATUS_SELESAI);
        PesananItem::create([
            'pesanan_id' => $pesanan->id,
            'nama_menu_snapshot' => 'Nasi Goreng Spesial',
            'harga' => 15000,
            'harga_snapshot' => 15000,
            'jumlah' => 2,
            'subtotal' => 30000,
        ]);

        $this->actingAs($this->kasir)
            ->get(route('kasir.rekap'))
            ->assertOk()
            ->assertSee('Nasi Goreng Spesial');
    }

    public function test_rekap_memfilter_berdasarkan_tanggal(): void
    {
        $this->buatPesanan('ORD-HARI-INI', 'Lunas', Pesanan::STATUS_SELESAI);
        $this->buatPesanan('ORD-KEMARIN', 'Lunas', Pesanan::STATUS_SELESAI, 20000, 'cash', now()->subDay()->toDateTimeString());

        $this->actingAs($this->kasir)
            ->get(route('kasir.rekap', ['tanggal' => now()->subDay()->toDateString()]))
            ->assertOk()
            ->assertSee('ORD-KEMARIN')
            ->assertDontSee('ORD-HARI-INI');
    }

    public function test_customer_tidak_bisa_akses_rekap_kasir(): void
    {
        $user = User::factory()->create();
        Customer::create([
            'user_id' => $user->id,
            'nim' => '11112222',
            'nama' => 'Cust Biasa',
            'kelas' => 'A',
        ]);

        // RoleMiddleware mengalihkan (bukan 403) ke dashboard sesuai peran.
        $this->actingAs($user->fresh())
            ->get(route('kasir.rekap'))
            ->assertRedirect(route('customer.dashboard'));
    }
}
