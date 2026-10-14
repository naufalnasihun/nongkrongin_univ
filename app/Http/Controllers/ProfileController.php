<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Profil pengguna: ubah identitas & ganti password.
 * Diakses oleh semua role (admin, kasir, customer).
 */
class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil.
     */
    public function edit()
    {
        $user = Auth::user()->load(['admin', 'kasir', 'customer']);

        return view('profile.edit', compact('user'));
    }

    /**
     * Perbarui data profil (nama/email + identitas sesuai role).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
        ];

        if ($user->customer) {
            $rules['nama'] = ['required', 'string', 'max:100'];
            $rules['nim'] = ['required', 'string', 'max:30', 'unique:customers,nim,'.$user->customer->id];
            $rules['kelas'] = ['nullable', 'string', 'max:30'];
        }

        if ($user->kasir) {
            $rules['nama'] = ['required', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->customer) {
            $user->customer->update([
                'nama' => $validated['nama'],
                'nim' => $validated['nim'],
                'kelas' => $validated['kelas'] ?? null,
            ]);
        } elseif ($user->kasir && isset($validated['nama'])) {
            $user->kasir->update(['nama' => $validated['nama']]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password pengguna yang sedang login.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diganti.');
    }
}
