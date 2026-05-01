<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!$request->user()) {
            return redirect('/login');
        }

        // Cek apakah role user ada di dalam daftar role yang diizinkan untuk rute ini
        if (!in_array($request->user()->role, $roles)) {
            // Jika tidak cocok, tolak aksesnya
            abort(403, 'Akses Ditolak: Halaman ini bukan untuk role Anda.');
        }

        return $next($request);
    }
}