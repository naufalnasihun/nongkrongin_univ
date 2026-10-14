<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kantin;
use App\Models\Menu;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Beranda customer: pemilihan kantin & warung (grouping).
 */
class CustomerDashboardKantinTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Kantin $kantinA;

    private Kantin $kantinB;

    private Warung $warungA1;

    private Warung $warungB1;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Customer::create(['user_id' => $user->id, 'nim' => '12341234', 'nama' => 'Cust K', 'kelas' => 'A']);
        $this->customer = $user->fresh();

        $this->kantinA = Kantin::create(['nama_kantin' => 'Kantin A']);
        $this->kantinB = Kantin::create(['nama_kantin' => 'Kantin B']);

        $this->warungA1 = Warung::create(['kantin_id' => $this->kantinA->id, 'nama_warung' => 'Warung A1', 'pemilik' => 'A1']);
        $this->warungB1 = Warung::create(['kantin_id' => $this->kantinB->id, 'nama_warung' => 'Warung B1', 'pemilik' => 'B1']);

        Menu::create([
            'warung_id' => $this->warungA1->id,
            'nama_menu' => 'Nasi A1',
            'kategori' => 'makanan',
            'harga' => 12000,
            'stok' => 10,
            'ketersediaan' => true,
        ]);

        Menu::create([
            'warung_id' => $this->warungB1->id,
            'nama_menu' => 'Es B1',
            'kategori' => 'minuman',
            'harga' => 5000,
            'stok' => 10,
            'ketersediaan' => true,
        ]);
    }

    public function test_beranda_menampilkan_pilihan_kantin(): void
    {
        $this->actingAs($this->customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Pilih Kantin')
            ->assertSee('Kantin A')
            ->assertSee('Kantin B')
            ->assertSee('Semua Kantin');
    }

    public function test_semua_menu_tampil_tanpa_filter(): void
    {
        $this->actingAs($this->customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Nasi A1')
            ->assertSee('Es B1');
    }

    public function test_filter_kantin_hanya_menampilkan_menu_kantin_itu(): void
    {
        $this->actingAs($this->customer)
            ->get(route('customer.dashboard', ['kantin_id' => $this->kantinA->id]))
            ->assertOk()
            ->assertSee('Nasi A1')
            ->assertDontSee('Es B1');
    }

    public function test_filter_kantin_menampilkan_pilihan_warung(): void
    {
        $this->actingAs($this->customer)
            ->get(route('customer.dashboard', ['kantin_id' => $this->kantinA->id]))
            ->assertOk()
            ->assertSee('Pilih Warung')
            ->assertSee('Warung A1')
            // Warung dari kantin lain tidak boleh muncul.
            ->assertDontSee('Warung B1');
    }

    public function test_filter_warung_menyaring_menu(): void
    {
        $this->actingAs($this->customer)
            ->get(route('customer.dashboard', [
                'kantin_id' => $this->kantinA->id,
                'warung_id' => $this->warungA1->id,
            ]))
            ->assertOk()
            ->assertSee('Nasi A1')
            ->assertDontSee('Es B1');
    }
}
