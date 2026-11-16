<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan oleh Administrator.']);
        }

        $userRole = strtolower($user->role->name ?? '');

        // If specific roles required, check
        if (!empty($roles)) {
            $allowed = array_map('strtolower', $roles);
            if (!in_array($userRole, $allowed)) {
                // If user visits wrong module, gracefully redirect to their authorized module
                return match ($userRole) {
                    'kasir' => redirect()->route('kasir.pos')->with('error', 'Akses ditolak. Anda dialihkan ke modul Kasir.'),
                    'gudang' => redirect()->route('gudang.products')->with('error', 'Akses ditolak. Anda dialihkan ke modul Gudang.'),
                    'admin' => redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Anda dialihkan ke dashboard Admin.'),
                    default => abort(403, 'Akses tidak diizinkan untuk peran Anda.'),
                };
            }
        }

        return $next($request);
    }
}

