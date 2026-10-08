<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index()
    {
        $users = User::with(['admin', 'kasir', 'customer'])
            ->latest()
            ->get();

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => 'required|email|max:255|unique:users,email',

            'password' => 'required|string|min:6',

            'role' => 'required|in:Admin,Kasir,Customer',

            'nama' => 'required|string|max:255',

            'nim' => [
                'required_if:role,Customer',
                'nullable',
                'string',
                'max:255',
                'unique:customers,nim',
            ],

            'kelas' => [
                'required_if:role,Customer',
                'nullable',
                'string',
                'max:255',
            ],

            'id_kasir' => [
                'required_if:role,Kasir',
                'nullable',
                'string',
                'max:255',
                'unique:kasirs,id_kasir',
            ],

            'id_admin' => [
                'required_if:role,Admin',
                'nullable',
                'string',
                'max:255',
                'unique:admins,id_admin',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            // Membuat akun User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Jika role Admin
            if ($validated['role'] === 'Admin') {

                Admin::create([
                    'user_id' => $user->id,
                    'id_admin' => $validated['id_admin'],
                    'nama' => $validated['nama'],
                ]);
            }

            // Jika role Kasir
            elseif ($validated['role'] === 'Kasir') {

                Kasir::create([
                    'user_id' => $user->id,
                    'id_kasir' => $validated['id_kasir'],
                    'nama' => $validated['nama'],
                ]);
            }

            // Jika role Customer
            elseif ($validated['role'] === 'Customer') {

                Customer::create([
                    'user_id' => $user->id,
                    'nama' => $validated['nama'],
                    'nim' => $validated['nim'],
                    'kelas' => $validated['kelas'],
                ]);
            }
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
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => 'nullable|string|min:6',

            'role' => 'required|in:Admin,Kasir,Customer',

            'nama' => 'required|string|max:255',

            'nim' => [
                'required_if:role,Customer',
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers', 'nim')->ignore(
                    optional($user->customer)->id
                ),
            ],

            'kelas' => [
                'required_if:role,Customer',
                'nullable',
                'string',
                'max:255',
            ],

            'id_kasir' => [
                'required_if:role,Kasir',
                'nullable',
                'string',
                'max:255',
                Rule::unique('kasirs', 'id_kasir')->ignore(
                    optional($user->kasir)->id
                ),
            ],

            'id_admin' => [
                'required_if:role,Admin',
                'nullable',
                'string',
                'max:255',
                Rule::unique('admins', 'id_admin')->ignore(
                    optional($user->admin)->id
                ),
            ],
        ]);

        DB::transaction(function () use ($request, $user) {

            // Update data akun
            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Hapus profile role lama
            Admin::where('user_id', $user->id)->delete();
            Kasir::where('user_id', $user->id)->delete();
            Customer::where('user_id', $user->id)->delete();

            // Buat profile sesuai role baru
            if ($request->role === 'Admin') {

                Admin::create([
                    'user_id' => $user->id,
                    'id_admin' => $request->id_admin,
                    'nama' => $request->nama,
                ]);
            }

            elseif ($request->role === 'Kasir') {

                Kasir::create([
                    'user_id' => $user->id,
                    'id_kasir' => $request->id_kasir,
                    'nama' => $request->nama,
                ]);
            }

            elseif ($request->role === 'Customer') {

                Customer::create([
                    'user_id' => $user->id,
                    'nama' => $request->nama,
                    'nim' => $request->nim,
                    'kelas' => $request->kelas,
                ]);
            }
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
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}