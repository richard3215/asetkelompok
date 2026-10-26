<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Tampilkan notifikasi pengguna yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        $daftar = Notifikasi::where('pengguna_id', $pengguna->id)
            ->latest('id')
            ->paginate(20);

        $belumDibaca = Notifikasi::where('pengguna_id', $pengguna->id)
            ->where('sudah_dibaca', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'pesan' => 'Notifikasi berhasil dimuat.',
            'jumlah_belum_dibaca' => $belumDibaca,
            'data' => $daftar,
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca.
     */
    public function baca(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $notifikasi = Notifikasi::where('pengguna_id', $pengguna->id)->findOrFail($id);

        $notifikasi->update([
            'sudah_dibaca' => true,
            'waktu_dibaca' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Notifikasi ditandai telah dibaca.',
        ]);
    }

    /**
     * Tandai seluruh notifikasi pengguna sebagai telah dibaca.
     */
    public function bacaSemua(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        Notifikasi::where('pengguna_id', $pengguna->id)
            ->where('sudah_dibaca', false)
            ->update([
                'sudah_dibaca' => true,
                'waktu_dibaca' => now(),
            ]);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Seluruh notifikasi telah ditandai dibaca.',
        ]);
    }
}
