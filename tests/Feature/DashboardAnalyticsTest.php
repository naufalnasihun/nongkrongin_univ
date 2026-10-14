<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\User;
use App\Models\Warung;
use App\Services\DashboardAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Admin::create(['user_id' => $user->id, 'id_admin' => 'ADM-AN', 'nama' => 'Admin Analitik']);
        $this->admin = $user->fresh();
    }

    private function seedPesanan(string $tanggal, string $status, string $bayar, float $total, int $jumlahItem = 1): Pesanan
    {
        $cu = User::factory()->create();
        $customer = Customer::create(['user_id' => $cu->id, 'nim' => '8'.uniqid(), 'nama' => 'An', 'kelas' => 'A']);
        $kantin = Kantin::create(['nama_kantin' => 'Kantin An']);
        $warung = Warung::create(['kantin_id' => $kantin->id, 'nama_warung' => 'Warung An']);
        $menu = Menu::create([
            'warung_id' => $warung->id, 'nama_menu' => 'Menu Analitik', 'kategori' => 'makanan',
            'harga' => 10000, 'stok' => 100, 'ketersediaan' => true,
        ]);

        $pesanan = Pesanan::create([
            'customer_id' => $customer->id,
            'warung_id' => $warung->id,
            'nomor_pesanan' => 'NRG-AN-'.uniqid(),
            'tanggal' => $tanggal,
            'total_harga' => $total,
            'status' => $status,
            'metode_bayar' => 'cash',
            'status_bayar' => $bayar,
        ]);

        PesananItem::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'nama_menu_snapshot' => 'Menu Analitik',
            'jumlah' => $jumlahItem,
            'harga' => 10000,
            'harga_snapshot' => 10000,
            'subtotal' => $total,
        ]);

        return $pesanan;
    }

    public function test_halaman_dashboard_menampilkan_semua_seksi_analitik(): void
    {
        $this->seedPesanan(now()->toDateTimeString(), 'Selesai', 'Lunas', 15000, 2);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tren Pendapatan')
            ->assertSee('Jam Tersibuk')
            ->assertSee('Menu Terlaris')
            ->assertSee('Stok Rendah');
    }

    public function test_tren_penjualan_jumlah_hari_sesuai(): void
    {
        $service = app(DashboardAnalyticsService::class);

        $this->assertCount(7, $service->trenPenjualan(7)['labels']);
        $this->assertCount(14, $service->trenPenjualan(14)['labels']);
        $this->assertCount(30, $service->trenPenjualan(30)['labels']);
    }

    public function test_tren_menghitung_pendapatan_lunas_saja(): void
    {
        $this->seedPesanan(now()->toDateTimeString(), 'Selesai', 'Lunas', 50000);
        $this->seedPesanan(now()->toDateTimeString(), 'Menunggu', 'Belum Bayar', 30000);

        $tren = app(DashboardAnalyticsService::class)->trenPenjualan(7);

        // Hari terakhir hanya menghitung yang lunas.
        $this->assertSame(50000.0, end($tren['pendapatan']));
    }

    public function test_perbandingan_periode_menghitung_delta(): void
    {
        // Periode kini (7 hari terakhir).
        $this->seedPesanan(now()->subDays(1)->toDateTimeString(), 'Selesai', 'Lunas', 100000);
        // Periode sebelumnya (7-14 hari lalu).
        $this->seedPesanan(now()->subDays(10)->toDateTimeString(), 'Selesai', 'Lunas', 50000);

        $banding = app(DashboardAnalyticsService::class)->bandingkanPeriode(7);

        $this->assertSame(100000.0, $banding['pendapatan']['now']);
        $this->assertSame(50000.0, $banding['pendapatan']['prev']);
        $this->assertSame(100.0, $banding['pendapatan']['delta'], 'Naik 100% dari 50k ke 100k.');
    }

    public function test_perbandingan_tanpa_data_sebelumnya_delta_100(): void
    {
        $this->seedPesanan(now()->subDays(1)->toDateTimeString(), 'Selesai', 'Lunas', 20000);

        $banding = app(DashboardAnalyticsService::class)->bandingkanPeriode(7);

        $this->assertSame(100.0, $banding['pendapatan']['delta']);
    }

    public function test_jam_sibuk_mengembalikan_rentang_jam(): void
    {
        $jamSibuk = app(DashboardAnalyticsService::class)->jamSibuk(30);

        $this->assertCount(14, $jamSibuk, 'Rentang jam 08:00 - 21:00 = 14 slot.');
        $this->assertSame('08:00', $jamSibuk[0]['label']);
        $this->assertSame('21:00', end($jamSibuk)['label']);
    }

    public function test_menu_terlaris_diurutkan_berdasarkan_jumlah(): void
    {
        $this->seedPesanan(now()->toDateTimeString(), 'Selesai', 'Lunas', 30000, 3);

        $terlaris = app(DashboardAnalyticsService::class)->menuTerlaris(30, 5);

        $this->assertTrue($terlaris->count() >= 1);
        $this->assertSame(3, $terlaris->first()->terjual);
    }

    public function test_stok_rendah_mendeteksi_menu_habis_dan_tipis(): void
    {
        $kantin = Kantin::create(['nama_kantin' => 'Kantin SR']);
        $warung = Warung::create(['kantin_id' => $kantin->id, 'nama_warung' => 'Warung SR']);

        Menu::create(['warung_id' => $warung->id, 'nama_menu' => 'Habis', 'kategori' => 'makanan', 'harga' => 1000, 'stok' => 0, 'ketersediaan' => false]);
        Menu::create(['warung_id' => $warung->id, 'nama_menu' => 'Tipis', 'kategori' => 'makanan', 'harga' => 1000, 'stok' => 3, 'ketersediaan' => true]);
        Menu::create(['warung_id' => $warung->id, 'nama_menu' => 'Aman', 'kategori' => 'makanan', 'harga' => 1000, 'stok' => 100, 'ketersediaan' => true]);

        $stokRendah = app(DashboardAnalyticsService::class)->stokRendah(10, 10);

        $this->assertSame(2, $stokRendah->count());
        $this->assertTrue($stokRendah->pluck('nama_menu')->contains('Habis'));
        $this->assertTrue($stokRendah->pluck('nama_menu')->contains('Tipis'));
        $this->assertFalse($stokRendah->pluck('nama_menu')->contains('Aman'));
    }

    public function test_dashboard_tidak_error_saat_kosong(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_parameter_hari_tidak_valid_diabaikan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['hari' => 999]))
            ->assertOk()
            ->assertViewHas('hari', 14);
    }
}
