<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(string $role): User
    {
        $user = User::factory()->create();

        match ($role) {
            'admin' => Admin::create(['user_id' => $user->id, 'id_admin' => 'ADM'.uniqid(), 'nama' => 'Admin']),
            'kasir' => Kasir::create(['user_id' => $user->id, 'id_kasir' => 'KSR'.uniqid(), 'nama' => 'Kasir']),
            'customer' => Customer::create(['user_id' => $user->id, 'nim' => 'NIM'.uniqid(), 'nama' => 'Cust', 'kelas' => 'A']),
        };

        return $user->fresh();
    }

    public function test_admin_bisa_akses_dashboard_admin(): void
    {
        $admin = $this->buatUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_customer_tidak_bisa_akses_halaman_admin(): void
    {
        $customer = $this->buatUser('customer');

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('customer.dashboard'));
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }
}
