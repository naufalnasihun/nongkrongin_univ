<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password salah.',
                ])
                ->withInput();
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->admin) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->kasir) {
            return redirect()->route('kasir.dashboard');
        }

        if ($user->customer) {
            return redirect()->route('customer.dashboard');
        }

        Auth::logout();

        return redirect()->route('login')
            ->withErrors([
                'email' => 'Akun belum memiliki role.',
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}