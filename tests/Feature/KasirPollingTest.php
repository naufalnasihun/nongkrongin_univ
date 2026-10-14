<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Kasir;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirPollingTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Kasir::create(['user_id' => $user->id, 'id_kasir' => 'KSR-POL', 'nama' => 'Kasir Poll']);
        $this->kasir = $user->fresh();
    }

    private function buatPesanan(string $status = 'Menunggu', ?string $statusBayar = 'Belum Bayar'): Pesanan
    {
        $cu = User::factory()->create();
        // NIM unik: turunkan dari id user (monoton naik, tidak bentrok).
        $customer = Customer::create(['user_id' => $cu->id, 'nim' => '9'.str_pad((string) $cu->id, 9, '0', STR_PAD_LEFT), 'nama' => 'Poll', 'kelas' => 'A']);
        $kantin = Kantin::create(['nama_kantin' => 'Kantin Poll '.$cu->id]);
        $warung = Warung::create(['kantin_id' => $kantin->id, 'nama_warung' => 'Warung Poll '.$cu->id]);

        return Pesanan::create([
            'customer_id' => $customer->id,
            'warung_id' => $warung->id,
            'nomor_pesanan' => 'NRG-POL-'.uniqid(),
            'tanggal' => now(),
            'total_harga' => 10000,
            'status' => $status,
            'metode_bayar' => 'cash',
            'status_bayar' => $statusBayar,
        ]);
    }

    public function test_polling_mengembalikan_jumlah_status(): void
    {
        $this->buatPesanan('Menunggu');
        $this->buatPesanan('Menunggu');
        $this->buatPesanan('Diproses');
        $this->buatPesanan('Siap Diambil');
        $this->buatPesanan('Selesai', 'Lunas');

        $this->actingAs($this->kasir)
            ->getJson(route('kasir.polling'))
            ->assertOk()
            ->assertJson([
                'counts' => [
                    'masuk' => 2,
                    'diproses' => 1,
                    'siap' => 1,
                    'total_aktif' => 4,
                ],
            ]);
    }

    public function test_polling_mengembalikan_pesanan_baru_sejak_id(): void
    {
        $lama = $this->buatPesanan('Selesai', 'Lunas');
        $baru = $this->buatPesanan('Menunggu');

        $this->actingAs($this->kasir)
            ->getJson(route('kasir.polling', ['since_id' => $lama->id]))
            ->assertOk()
            ->assertJsonCount(1, 'baru')
            ->assertJsonPath('baru.0.id', $baru->id)
            ->assertJsonPath('baru.0.nomor_pesanan', $baru->nomor_pesanan);
    }

    public function test_polling_tidak_menampilkan_yang_bukan_menunggu(): void
    {
        $lama = $this->buatPesanan('Selesai', 'Lunas');
        $this->buatPesanan('Diproses');

        $this->actingAs($this->kasir)
            ->getJson(route('kasir.polling', ['since_id' => $lama->id]))
            ->assertOk()
            ->assertJsonCount(0, 'baru');
    }

    public function test_hanya_kasir_yang_bisa_akses_polling(): void
    {
        $adminUser = User::factory()->create();
        Admin::create(['user_id' => $adminUser->id, 'id_admin' => 'ADM-POL', 'nama' => 'Admin']);
        $admin = $adminUser->fresh();

        $this->actingAs($admin)
            ->get(route('kasir.polling'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_tamu_tidak_bisa_akses_polling(): void
    {
        $this->getJson(route('kasir.polling'))->assertUnauthorized();
    }
}
