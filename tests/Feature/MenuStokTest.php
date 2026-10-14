<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Kantin;
use App\Models\Kasir;
use App\Models\Menu;
use App\Models\User;
use App\Models\Warung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuStokTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private function buatMenu(int $stok = 20, bool $tersedia = true): Menu
    {
        $kantin = Kantin::create(['nama_kantin' => 'Kantin Stok']);
        $warung = Warung::create(['kantin_id' => $kantin->id, 'nama_warung' => 'Warung Stok']);

        return Menu::create([
            'warung_id' => $warung->id,
            'nama_menu' => 'Menu '.uniqid(),
            'kategori' => 'makanan',
            'harga' => 10000,
            'stok' => $stok,
            'ketersediaan' => $tersedia,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Admin::create(['user_id' => $user->id, 'id_admin' => 'ADM-STOK', 'nama' => 'Admin Stok']);
        $this->admin = $user->fresh();
    }

    public function test_bulk_set_stok(): void
    {
        $a = $this->buatMenu(5);
        $b = $this->buatMenu(8);

        $this->actingAs($this->admin)
            ->patch(route('admin.menus.bulk-stok'), [
                'aksi' => 'set',
                'nilai' => 15,
                'menu_ids' => [$a->id, $b->id],
            ])
            ->assertSessionHas('success');

        $this->assertSame(15, $a->fresh()->stok);
        $this->assertSame(15, $b->fresh()->stok);
    }

    public function test_bulk_tambah_dan_kurang_stok(): void
    {
        $menu = $this->buatMenu(10);

        $this->actingAs($this->admin)->patch(route('admin.menus.bulk-stok'), [
            'aksi' => 'tambah', 'nilai' => 5, 'menu_ids' => [$menu->id],
        ]);
        $this->assertSame(15, $menu->fresh()->stok);

        $this->actingAs($this->admin)->patch(route('admin.menus.bulk-stok'), [
            'aksi' => 'kurang', 'nilai' => 3, 'menu_ids' => [$menu->id],
        ]);
        $this->assertSame(12, $menu->fresh()->stok);
    }

    public function test_bulk_kurang_tidak_boleh_negatif(): void
    {
        $menu = $this->buatMenu(4);

        $this->actingAs($this->admin)->patch(route('admin.menus.bulk-stok'), [
            'aksi' => 'kurang', 'nilai' => 100, 'menu_ids' => [$menu->id],
        ]);

        $this->assertSame(0, $menu->fresh()->stok, 'Stok tidak boleh negatif.');
    }

    public function test_set_stok_nol_menandai_habis(): void
    {
        $menu = $this->buatMenu(10, true);

        $this->actingAs($this->admin)->patch(route('admin.menus.bulk-stok'), [
            'aksi' => 'set', 'nilai' => 0, 'menu_ids' => [$menu->id],
        ]);

        $menu->refresh();
        $this->assertSame(0, $menu->stok);
        $this->assertFalse((bool) $menu->ketersediaan, 'Stok 0 harus jadi tidak tersedia.');
    }

    public function test_set_stok_dari_habis_menjadi_tersedia(): void
    {
        $menu = $this->buatMenu(0, false);

        $this->actingAs($this->admin)->patch(route('admin.menus.bulk-stok'), [
            'aksi' => 'set', 'nilai' => 5, 'menu_ids' => [$menu->id],
        ]);

        $menu->refresh();
        $this->assertSame(5, $menu->stok);
        $this->assertTrue((bool) $menu->ketersediaan);
    }

    public function test_bulk_stok_wajib_pilih_menu(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.menus.bulk-stok'), ['aksi' => 'set', 'nilai' => 10])
            ->assertSessionHasErrors('menu_ids');
    }

    public function test_bulk_stok_validasi_nilai(): void
    {
        $menu = $this->buatMenu(5);

        $this->actingAs($this->admin)
            ->patch(route('admin.menus.bulk-stok'), [
                'aksi' => 'set', 'nilai' => -1, 'menu_ids' => [$menu->id],
            ])
            ->assertSessionHasErrors('nilai');

        $this->actingAs($this->admin)
            ->patch(route('admin.menus.bulk-stok'), [
                'aksi' => 'aneh', 'nilai' => 5, 'menu_ids' => [$menu->id],
            ])
            ->assertSessionHasErrors('aksi');
    }

    public function test_filter_stok_rendah(): void
    {
        $this->buatMenu(3);
        $this->buatMenu(0, false);
        $this->buatMenu(50);

        $resp = $this->actingAs($this->admin)->get(route('admin.menus.index', ['stok_rendah' => 1]));

        $resp->assertOk();
        // Hanya 2 menu (stok 3 dan 0) yang muncul.
        $this->assertSame(2, $resp->viewData('menus')->total());
    }

    public function test_kasir_tidak_bisa_bulk_stok(): void
    {
        $menu = $this->buatMenu(5);

        $kasirUser = User::factory()->create();
        Kasir::create(['user_id' => $kasirUser->id, 'id_kasir' => 'KSR-STOK', 'nama' => 'Kasir']);
        $kasir = $kasirUser->fresh();

        $this->actingAs($kasir)
            ->patch(route('admin.menus.bulk-stok'), [
                'aksi' => 'set', 'nilai' => 99, 'menu_ids' => [$menu->id],
            ])
            ->assertRedirect(route('kasir.dashboard'));

        $this->assertSame(5, $menu->fresh()->stok, 'Kasir tidak boleh mengubah stok.');
    }
}
