<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filter riwayat pesanan milik customer.
 */
class CustomerOrdersFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private int $customerId;

    private Warung $warung;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'nim' => '88990011',
            'nama' => 'Customer Filter',
            'kelas' => 'TI-4A',
        ]);
        $this->customerId = $customer->id;
        $this->customer = $user->fresh();

        $this->warung = Warung::create([
            'kantin_id' => Kantin::create(['nama_kantin' => 'Kantin CF'])->id,
            'nama_warung' => 'Warung CF',
        ]);
    }

    private function buatPesanan(string $nomor, string $status, string $statusBayar = 'Belum Bayar', string $tanggal = 'now'): Pesanan
    {
        return Pesanan::create([
            'customer_id' => $this->customerId,
            'warung_id' => $this->warung->id,
            'nomor_pesanan' => $nomor,
            'nomor_antrian' => 'A-'.random_int(100, 999),
            'status' => $status,
            'status_bayar' => $statusBayar,
            'metode_bayar' => 'cash',
            'total_harga' => 15000,
            'tanggal' => $tanggal === 'now' ? now() : $tanggal,
            'nama_customer_snapshot' => 'Customer Filter',
            'nim_snapshot' => '88990011',
        ]);
    }

    public function test_customer_hanya_melihat_pesanan_miliknya(): void
    {
        $this->buatPesanan('ORD-MILIK-SAYA', Pesanan::STATUS_MENUNGGU);

        // Pesanan milik customer lain.
        $otherUser = User::factory()->create();
        $otherCustomer = Customer::create([
            'user_id' => $otherUser->id,
            'nim' => '88990099',
            'nama' => 'Orang Lain',
            'kelas' => 'B',
        ]);
        Pesanan::create([
            'customer_id' => $otherCustomer->id,
            'warung_id' => $this->warung->id,
            'nomor_pesanan' => 'ORD-ORANG-LAIN',
            'status' => Pesanan::STATUS_MENUNGGU,
            'status_bayar' => 'Belum Bayar',
            'metode_bayar' => 'cash',
            'total_harga' => 10000,
            'tanggal' => now(),
        ]);

        $this->actingAs($this->customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('ORD-MILIK-SAYA')
            ->assertDontSee('ORD-ORANG-LAIN');
    }

    public function test_filter_status_menyaring_pesanan(): void
    {
        $this->buatPesanan('ORD-SELESAI-1', Pesanan::STATUS_SELESAI, 'Lunas');
        $this->buatPesanan('ORD-MENUNGGU-1', Pesanan::STATUS_MENUNGGU);

        $this->actingAs($this->customer)
            ->get(route('customer.orders.index', ['status' => Pesanan::STATUS_SELESAI]))
            ->assertOk()
            ->assertSee('ORD-SELESAI-1')
            ->assertDontSee('ORD-MENUNGGU-1');
    }

    public function test_filter_pembayaran_menyaring_pesanan(): void
    {
        $this->buatPesanan('ORD-LUNAS-1', Pesanan::STATUS_SELESAI, 'Lunas');
        $this->buatPesanan('ORD-BELUM-1', Pesanan::STATUS_MENUNGGU, 'Belum Bayar');

        $this->actingAs($this->customer)
            ->get(route('customer.orders.index', ['bayar' => 'Lunas']))
            ->assertOk()
            ->assertSee('ORD-LUNAS-1')
            ->assertDontSee('ORD-BELUM-1');
    }

    public function test_pencarian_nomor_pesanan(): void
    {
        $this->buatPesanan('ORD-UNIK-XYZ', Pesanan::STATUS_MENUNGGU);
        $this->buatPesanan('ORD-LAIN-ABC', Pesanan::STATUS_MENUNGGU);

        $this->actingAs($this->customer)
            ->get(route('customer.orders.index', ['search' => 'XYZ']))
            ->assertOk()
            ->assertSee('ORD-UNIK-XYZ')
            ->assertDontSee('ORD-LAIN-ABC');
    }

    public function test_filter_rentang_tanggal(): void
    {
        $this->buatPesanan('ORD-LAMA', Pesanan::STATUS_SELESAI, 'Lunas', now()->subDays(40)->toDateTimeString());
        $this->buatPesanan('ORD-BARU', Pesanan::STATUS_MENUNGGU);

        $this->actingAs($this->customer)
            ->get(route('customer.orders.index', [
                'dari' => now()->subDays(6)->toDateString(),
                'sampai' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('ORD-BARU')
            ->assertDontSee('ORD-LAMA');
    }

    public function test_pesan_kosong_saat_filter_tidak_ada_hasil(): void
    {
        $this->buatPesanan('ORD-ADA', Pesanan::STATUS_MENUNGGU);

        $this->actingAs($this->customer)
            ->get(route('customer.orders.index', ['search' => 'TIDAKADA-XYZ']))
            ->assertOk()
            ->assertSee('Tidak ada pesanan yang cocok');
    }
}
