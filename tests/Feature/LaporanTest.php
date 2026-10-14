<?php

namespace Tests\Feature;

use App\Http\Controllers\LaporanController;
use App\Models\Admin;
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

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Admin::create(['user_id' => $user->id, 'id_admin' => 'ADM-LAP', 'nama' => 'Admin Laporan']);
        $this->admin = $user->fresh();

        $customerUser = User::factory()->create();
        $customer = Customer::create(['user_id' => $customerUser->id, 'nim' => '2495114200', 'nama' => 'Lapor', 'kelas' => 'A']);

        $kantin = Kantin::create(['nama_kantin' => 'Kantin Laporan']);
        $warung = Warung::create(['kantin_id' => $kantin->id, 'nama_warung' => 'Warung Laporan']);
        $menu = Menu::create([
            'warung_id' => $warung->id,
            'nama_menu' => 'Menu Laporan',
            'kategori' => 'makanan',
            'harga' => 12000,
            'stok' => 50,
            'ketersediaan' => true,
        ]);

        $pesanan = Pesanan::create([
            'customer_id' => $customer->id,
            'warung_id' => $warung->id,
            'nomor_pesanan' => 'NRG-LAP-0001',
            'nomor_antrian' => 'A-001',
            'tanggal' => now(),
            'total_harga' => 24000,
            'status' => Pesanan::STATUS_SELESAI,
            'metode_bayar' => 'cash',
            'status_bayar' => Pesanan::BAYAR_LUNAS,
        ]);

        PesananItem::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'nama_menu_snapshot' => 'Menu Laporan',
            'jumlah' => 2,
            'harga' => 12000,
            'harga_snapshot' => 12000,
            'subtotal' => 24000,
        ]);
    }

    public function test_halaman_laporan_menampilkan_ringkasan(): void
    {

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSee('Laporan Penjualan')
            ->assertSee('Menu Laporan')
            ->assertSee('24.000');
    }

    public function test_halaman_laporan_tidak_error_saat_nol_pendapatan(): void
    {
        // Regresi: chart harian pernah menyebabkan DivisionByZeroError saat
        // seluruh pendapatan periode bernilai 0 (belum ada pesanan lunas).
        Pesanan::query()->update(['status_bayar' => Pesanan::BAYAR_BELUM]);

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSee('Penjualan Harian');
    }

    public function test_halaman_laporan_tanpa_data_sama_sekali(): void
    {
        PesananItem::query()->delete();
        Pesanan::query()->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSee('Belum ada data');
    }

    public function test_penjualan_harian_dikelompokkan_per_tanggal(): void
    {
        // Regresi: `groupBy('tanggal')` di-quote MySQL sebagai kolom datetime
        // penuh sehingga tiap jam jadi grup terpisah. Harus digabung per hari.
        $controller = new LaporanController;
        $method = new \ReflectionMethod($controller, 'penjualanHarian');
        $method->setAccessible(true);

        $rows = $method->invoke($controller, now()->toDateString(), now()->toDateString());

        $this->assertCount(1, $rows, 'Semua pesanan hari ini harus jadi satu baris.');
        $this->assertSame(1, $rows->first()->jumlah);
        $this->assertIsFloat($rows->first()->pendapatan, 'Pendapatan harus float, bukan string "0.00".');
    }

    public function test_export_csv_berisi_data(): void
    {
        $resp = $this->actingAs($this->admin)->get(route('admin.laporan.csv'));

        $resp->assertOk();
        $resp->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Laporan Penjualan', $resp->streamedContent());
    }

    public function test_export_excel_menghasilkan_file_xlsx(): void
    {
        $resp = $this->actingAs($this->admin)->get(route('admin.laporan.excel'));

        $resp->assertOk();
        $this->assertStringContainsString('.xlsx', $resp->headers->get('content-disposition'));
    }

    public function test_kasir_tidak_bisa_akses_laporan(): void
    {
        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR-LAP', 'nama' => 'Kasir']);
        $kasir = $kasirUser->fresh();

        $this->actingAs($kasir)
            ->get(route('admin.laporan.index'))
            ->assertRedirect(route('kasir.dashboard'));
    }
}
