<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filter daftar pesanan admin.
 *
 * Berisi regresi untuk bug "orWhere bocor": sebelumnya kombinasi
 * `search` + `status` mengabaikan filter status karena `orWhere`
 * tidak dikelompokkan dalam closure.
 */
class AdminOrdersFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Warung $warung;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create();
        Admin::create(['user_id' => $adminUser->id, 'id_admin' => 'ADM-FLT', 'nama' => 'Admin Filter']);
        $this->admin = $adminUser->fresh();

        $this->warung = Warung::create([
            'kantin_id' => Kantin::create(['nama_kantin' => 'Kantin Filter'])->id,
            'nama_warung' => 'Warung Filter',
        ]);
    }

    private function buatPesanan(string $namaCustomer, string $status, string $statusBayar = 'Belum Bayar'): Pesanan
    {
        $cu = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $cu->id,
            'nim' => '76'.random_int(10000, 99999),
            'nama' => $namaCustomer,
            'kelas' => 'A',
        ]);

        return Pesanan::create([
            'customer_id' => $customer->id,
            'warung_id' => $this->warung->id,
            'nomor_pesanan' => 'NRG-FLT-'.uniqid(),
            'tanggal' => now(),
            'total_harga' => 12000,
            'status' => $status,
            'metode_bayar' => 'cash',
            'status_bayar' => $statusBayar,
        ]);
    }

    public function test_filter_status_saja(): void
    {
        $this->buatPesanan('Budi', 'Selesai', 'Lunas');
        $this->buatPesanan('Siti', 'Menunggu');

        $resp = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'Selesai']));

        $resp->assertOk();
        $this->assertSame(1, $resp->viewData('pesanans')->total());
    }

    public function test_filter_status_bayar(): void
    {
        $this->buatPesanan('Budi', 'Selesai', 'Lunas');
        $this->buatPesanan('Siti', 'Menunggu', 'Belum Bayar');

        $resp = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status_bayar' => 'Lunas']));

        $resp->assertOk();
        $this->assertSame(1, $resp->viewData('pesanans')->total());
    }

    public function test_search_dan_status_tidak_saling_bocor(): void
    {
        // Semua pesanan milik customer bernama "Customer Filter", tetapi
        // tidak ada yang berstatus Dibatalkan.
        $this->buatPesanan('Customer Filter', 'Selesai', 'Lunas');
        $this->buatPesanan('Customer Filter', 'Menunggu');

        $resp = $this->actingAs($this->admin)->get(route('admin.orders.index', [
            'search' => 'Customer Filter',
            'status' => 'Dibatalkan',
        ]));

        $resp->assertOk();
        $this->assertSame(
            0,
            $resp->viewData('pesanans')->total(),
            'Filter status harus tetap berlaku walau ada search (orWhere tidak boleh bocor).'
        );
    }

    public function test_search_mencocokkan_nomor_atau_nama(): void
    {
        $this->buatPesanan('Nama Unik Abc', 'Menunggu');
        $this->buatPesanan('Lain', 'Menunggu');

        $byNama = $this->actingAs($this->admin)->get(route('admin.orders.index', ['search' => 'Nama Unik']));
        $this->assertSame(1, $byNama->viewData('pesanans')->total());

        $p = Pesanan::first();
        $byNomor = $this->actingAs($this->admin)->get(route('admin.orders.index', ['search' => $p->nomor_pesanan]));
        $this->assertSame(1, $byNomor->viewData('pesanans')->total());
    }

    public function test_halaman_orders_menampilkan_kolom_pembayaran(): void
    {
        $this->buatPesanan('Budi', 'Selesai', 'Lunas');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Lunas')
            ->assertSee('Pembayaran');
    }
}
