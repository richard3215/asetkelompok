<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$peran
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $pengguna = $request->user();

        if (!$pengguna) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak. Pengguna belum terautentikasi.',
            ], 401);
        }

        // Admin selalu memiliki akses ke semua endpoint
        if ($pengguna->memilikiPeran('admin')) {
            return $next($request);
        }

        if ($pengguna->peran && in_array($pengguna->peran->kode_peran, $peran)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'pesan' => 'Akses ditolak. Anda tidak memiliki kewenangan (role) yang diperlukan untuk aksi ini.',
            'peran_pengguna' => $pengguna->peran ? $pengguna->peran->nama_peran : null,
            'peran_diperlukan' => $peran,
        ], 403);
    }
}
