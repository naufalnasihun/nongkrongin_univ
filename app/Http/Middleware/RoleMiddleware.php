<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $hasRole = match ($role) {
            'admin' => $user->admin !== null,
            'kasir' => $user->kasir !== null,
            'customer' => $user->customer !== null,
            default => false,
        };

        if (! $hasRole) {
            if ($user->admin) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->kasir) {
                return redirect()->route('kasir.dashboard');
            }
            if ($user->customer) {
                return redirect()->route('customer.dashboard');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
