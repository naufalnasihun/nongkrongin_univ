<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Kasir;
use App\Models\Pesanan;
use App\Models\Topup;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Saldo deposit customer & top-up (konfirmasi kasir).
 */
class TopupSaldoTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;

    private Customer $customer;

    private User $kasirUser;

    private Warung $warung;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerUser = User::factory()->create();
        $this->customer = Customer::create([
            'user_id' => $this->customerUser->id,
            'nim' => '55556666',
            'nama' => 'Customer Saldo',
            'kelas' => 'A',
        ]);

        $kasir = User::factory()->create();
        Kasir::create(['user_id' => $kasir->id, 'id_kasir' => 'KSR-TOP', 'nama' => 'Kasir Top']);
        $this->kasirUser = $kasir->fresh();

        $this->warung = Warung::create([
            'kantin_id' => Kantin::create(['nama_kantin' => 'Kantin Top'])->id,
            'nama_warung' => 'Warung Top',
        ]);
    }

    private function buatPesanan(int $total = 15000, string $metode = 'deposit'): Pesanan
    {
        return Pesanan::create([
            'customer_id' => $this->customer->id,
            'warung_id' => $this->warung->id,
            'nomor_pesanan' => 'ORD-TOP-'.random_int(1000, 9999),
            'status' => Pesanan::STATUS_DIPROSES,
            'status_bayar' => 'Belum Bayar',
            'metode_bayar' => $metode,
            'total_harga' => $total,
            'tanggal' => now(),
        ]);
    }

    public function test_customer_bisa_lihat_halaman_saldo(): void
    {
        $this->actingAs($this->customerUser)
            ->get(route('customer.saldo.index'))
            ->assertOk()
            ->assertSee('Saldo Aktif');
    }

    public function test_customer_mengajukan_topup(): void
    {
        $this->actingAs($this->customerUser)
            ->post(route('customer.saldo.topup'), ['jumlah' => 50000, 'metode' => 'qris'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('topups', [
            'customer_id' => $this->customer->id,
            'status' => Topup::STATUS_MENUNGGU,
        ]);
    }

    public function test_topup_ditolak_bila_masih_ada_yang_menunggu(): void
    {
        Topup::create([
            'customer_id' => $this->customer->id,
            'jumlah' => 20000,
            'metode' => 'qris',
            'status' => Topup::STATUS_MENUNGGU,
        ]);

        $this->actingAs($this->customerUser)
            ->post(route('customer.saldo.topup'), ['jumlah' => 30000, 'metode' => 'qris'])
            ->assertSessionHas('error');

        $this->assertSame(1, Topup::count());
    }

    public function test_topup_minimal_seribu_rupiah(): void
    {
        $this->actingAs($this->customerUser)
            ->post(route('customer.saldo.topup'), ['jumlah' => 500, 'metode' => 'qris'])
            ->assertSessionHasErrors('jumlah');
    }

    public function test_kasir_menyetujui_topup_menambah_saldo(): void
    {
        $topup = Topup::create([
            'customer_id' => $this->customer->id,
            'jumlah' => 100000,
            'metode' => 'qris',
            'status' => Topup::STATUS_MENUNGGU,
        ]);

        $this->actingAs($this->kasirUser)
            ->patch(route('kasir.topup.setujui', $topup))
            ->assertSessionHas('success');

        $this->assertEquals(100000, (float) $this->customer->fresh()->saldo);
        $this->assertSame(Topup::STATUS_DISETUJUI, $topup->fresh()->status);
    }

    public function test_kasir_menolak_topup_tidak_menambah_saldo(): void
    {
        $topup = Topup::create([
            'customer_id' => $this->customer->id,
            'jumlah' => 100000,
            'metode' => 'qris',
            'status' => Topup::STATUS_MENUNGGU,
        ]);

        $this->actingAs($this->kasirUser)
            ->patch(route('kasir.topup.tolak', $topup))
            ->assertSessionHas('success');

        $this->assertEquals(0, (float) $this->customer->fresh()->saldo);
        $this->assertSame(Topup::STATUS_DITOLAK, $topup->fresh()->status);
    }

    public function test_bayar_deposit_memotong_saldo(): void
    {
        $this->customer->update(['saldo' => 50000]);
        $pesanan = $this->buatPesanan(total: 15000, metode: 'deposit');

        $this->actingAs($this->kasirUser)
            ->patch(route('kasir.orders.paid', $pesanan), ['metode_bayar' => 'deposit'])
            ->assertSessionHas('success');

        $this->assertEquals(35000, (float) $this->customer->fresh()->saldo);
        $this->assertSame(Pesanan::BAYAR_LUNAS, $pesanan->fresh()->status_bayar);
    }

    public function test_bayar_deposit_ditolak_bila_saldo_kurang(): void
    {
        $this->customer->update(['saldo' => 5000]);
        $pesanan = $this->buatPesanan(total: 15000, metode: 'deposit');

        $this->actingAs($this->kasirUser)
            ->patch(route('kasir.orders.paid', $pesanan), ['metode_bayar' => 'deposit'])
            ->assertSessionHas('error');

        $this->assertEquals(5000, (float) $this->customer->fresh()->saldo);
        $this->assertSame('Belum Bayar', $pesanan->fresh()->status_bayar);
    }

    public function test_bayar_tunai_tidak_memotong_saldo(): void
    {
        $this->customer->update(['saldo' => 50000]);
        $pesanan = $this->buatPesanan(total: 15000, metode: 'cash');

        $this->actingAs($this->kasirUser)
            ->patch(route('kasir.orders.paid', $pesanan), ['metode_bayar' => 'cash'])
            ->assertSessionHas('success');

        $this->assertEquals(50000, (float) $this->customer->fresh()->saldo);
    }

    public function test_customer_tidak_bisa_akses_konfirmasi_topup_kasir(): void
    {
        $this->actingAs($this->customerUser)
            ->get(route('kasir.topup.index'))
            ->assertRedirect(route('customer.dashboard'));
    }
}
