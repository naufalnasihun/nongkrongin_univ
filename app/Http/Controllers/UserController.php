<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index(Request $request)
    {
        $query = User::with(['admin', 'kasir', 'customer'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            match ($request->role) {
                'Admin' => $query->whereHas('admin'),
                'Kasir' => $query->whereHas('kasir'),
                'Customer' => $query->whereHas('customer'),
                default => null,
            };
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $this->syncProfile($user, $validated);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        $user->load(['admin', 'kasir', 'customer']);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna.
     *
     * PENTING: profil role (admin/kasir/customer) di-update secara in-place,
     * bukan dihapus-dan-dibuat-ulang. Sebelumnya pola delete-recreate membuat
     * seluruh riwayat pesanan customer ikut terhapus lewat cascade FK.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $user) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            $this->syncProfile($user, $validated);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        // Cegah admin menghapus akunnya sendiri.
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Sinkronkan profil role tanpa menghapus data yang sudah ada.
     *
     * - Role aktif: update in-place bila sudah ada, create bila belum.
     * - Role lain yang tidak lagi dipakai: hanya dihapus bila aman
     *   (tidak punya riwayat pesanan/keranjang) agar tidak memutus FK.
     */
    private function syncProfile(User $user, array $data): void
    {
        $role = $data['role'];

        if ($role === 'Admin') {
            Admin::updateOrCreate(
                ['user_id' => $user->id],
                ['id_admin' => $data['id_admin'] ?? null, 'nama' => $data['nama']],
            );
        } elseif ($role === 'Kasir') {
            Kasir::updateOrCreate(
                ['user_id' => $user->id],
                ['id_kasir' => $data['id_kasir'] ?? null, 'nama' => $data['nama']],
            );
        } elseif ($role === 'Customer') {
            Customer::updateOrCreate(
                ['user_id' => $user->id],
                ['nim' => $data['nim'], 'kelas' => $data['kelas'], 'nama' => $data['nama']],
            );
        }

        // Bersihkan profil role lain yang tidak lagi dipakai, tapi JANGAN hapus
        // bila masih memiliki transaksi (menjaga integritas riwayat).
        if ($role !== 'Admin') {
            $user->admin()->delete();
        }

        if ($role !== 'Kasir') {
            $kasir = $user->kasir;

            if ($kasir && ! $kasir->pesanans()->exists()) {
                $kasir->delete();
            }
        }

        if ($role !== 'Customer') {
            $customer = $user->customer;

            if ($customer && ! $customer->pesanans()->exists() && ! $customer->keranjang()->exists()) {
                $customer->delete();
            }
        }
    }
}
