<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah pengguna sudah login DAN memiliki role 'owner'
        if (auth()->check() && auth()->user()->role == 'owner') {
            // Jika ya, izinkan untuk melanjutkan ke halaman berikutnya
            return $next($request);
        }

        // Jika tidak, hentikan proses dan tampilkan halaman error 403 (Akses Ditolak)
        abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
    }
}