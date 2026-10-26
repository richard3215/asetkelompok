<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Otentikasi pengguna menggunakan Email atau NIP dan Kata Sandi.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identitas' => 'required|string',
            'kata_sandi' => 'required|string',
        ], [
            'identitas.required' => 'Email atau NIP wajib diisi.',
            'kata_sandi.required' => 'Kata sandi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $identitas = trim($request->identitas);
        $pengguna = Pengguna::with(['peran.hakAkses', 'kantor', 'divisi'])
            ->where(function ($q) use ($identitas) {
                $q->where('email', $identitas)
                  ->orWhere('nip', $identitas);
            })
            ->first();

        if (!$pengguna || !Hash::check($request->kata_sandi, $pengguna->kata_sandi)) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Kombinasi Email/NIP dan Kata Sandi tidak sesuai.',
            ], 401);
        }

        if (!$pengguna->status_aktif) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator Sistem.',
            ], 403);
        }

        // Hapus token lama jika perlu, buat token baru
        $token = $pengguna->createToken('android_auth_token')->plainTextToken;

        // Perbarui waktu login
        $pengguna->update([
            'terakhir_login' => now(),
        ]);

        AuditLogService::catat(
            'Autentikasi',
            'login',
            "Pengguna {$pengguna->nama} (NIP: {$pengguna->nip}) berhasil login via Android."
        );

        return response()->json([
            'status' => 'success',
            'pesan' => 'Login berhasil. Selamat datang di SI-ASET.',
            'data' => [
                'token' => $token,
                'pengguna' => [
                    'id' => $pengguna->id,
                    'nip' => $pengguna->nip,
                    'nama' => $pengguna->nama,
                    'email' => $pengguna->email,
                    'nomor_telepon' => $pengguna->nomor_telepon,
                    'foto_profil' => $pengguna->foto_profil ? asset('storage/' . $pengguna->foto_profil) : null,
                    'peran' => $pengguna->peran ? [
                        'id' => $pengguna->peran->id,
                        'kode' => $pengguna->peran->kode_peran,
                        'nama' => $pengguna->peran->nama_peran,
                    ] : null,
                    'kantor' => $pengguna->kantor ? [
                        'id' => $pengguna->kantor->id,
                        'kode' => $pengguna->kantor->kode_kantor,
                        'nama' => $pengguna->kantor->nama_kantor,
                        'jenis' => $pengguna->kantor->jenis_kantor,
                    ] : null,
                    'divisi' => $pengguna->divisi ? [
                        'id' => $pengguna->divisi->id,
                        'kode' => $pengguna->divisi->kode_divisi,
                        'nama' => $pengguna->divisi->nama_divisi,
                    ] : null,
                    'hak_akses' => $pengguna->peran ? $pengguna->peran->hakAkses->pluck('kode_hak_akses') : [],
                ],
            ],
        ]);
    }

    /**
     * Ambil informasi profil pengguna yang sedang login.
     */
    public function profil(Request $request): JsonResponse
    {
        $pengguna = $request->user()->load(['peran.hakAkses', 'kantor', 'divisi']);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data profil berhasil diambil.',
            'data' => [
                'id' => $pengguna->id,
                'nip' => $pengguna->nip,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'nomor_telepon' => $pengguna->nomor_telepon,
                'foto_profil' => $pengguna->foto_profil ? asset('storage/' . $pengguna->foto_profil) : null,
                'peran' => $pengguna->peran ? [
                    'id' => $pengguna->peran->id,
                    'kode' => $pengguna->peran->kode_peran,
                    'nama' => $pengguna->peran->nama_peran,
                ] : null,
                'kantor' => $pengguna->kantor ? [
                    'id' => $pengguna->kantor->id,
                    'kode' => $pengguna->kantor->kode_kantor,
                    'nama' => $pengguna->kantor->nama_kantor,
                    'jenis' => $pengguna->kantor->jenis_kantor,
                ] : null,
                'divisi' => $pengguna->divisi ? [
                    'id' => $pengguna->divisi->id,
                    'kode' => $pengguna->divisi->kode_divisi,
                    'nama' => $pengguna->divisi->nama_divisi,
                ] : null,
                'hak_akses' => $pengguna->peran ? $pengguna->peran->hakAkses->pluck('kode_hak_akses') : [],
            ],
        ]);
    }

    /**
     * Perbarui profil pengguna sendiri.
     */
    public function updateProfil(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        $validator = Validator::make($request->all(), [
            'nomor_telepon' => 'nullable|string|max:30',
            'kata_sandi_lama' => 'nullable|string|required_with:kata_sandi_baru',
            'kata_sandi_baru' => 'nullable|string|min:6',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->filled('kata_sandi_baru')) {
            if (!Hash::check($request->kata_sandi_lama, $pengguna->kata_sandi)) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Kata sandi lama tidak sesuai.',
                ], 422);
            }
            $pengguna->kata_sandi = Hash::make($request->kata_sandi_baru);
        }

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('profil', 'public');
            $pengguna->foto_profil = $path;
        }

        if ($request->has('nomor_telepon')) {
            $pengguna->nomor_telepon = $request->nomor_telepon;
        }

        $pengguna->save();

        AuditLogService::catat('Pengguna', 'update', "Pengguna {$pengguna->nama} memperbarui profil mandiri.");

        return response()->json([
            'status' => 'success',
            'pesan' => 'Profil berhasil diperbarui.',
            'data' => [
                'nomor_telepon' => $pengguna->nomor_telepon,
                'foto_profil' => $pengguna->foto_profil ? asset('storage/' . $pengguna->foto_profil) : null,
            ],
        ]);
    }

    /**
     * Logout dan cabut token saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        if ($pengguna) {
            AuditLogService::catat('Autentikasi', 'logout', "Pengguna {$pengguna->nama} logout.");
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'pesan' => 'Logout berhasil. Sesi token telah ditutup.',
        ]);
    }
}
