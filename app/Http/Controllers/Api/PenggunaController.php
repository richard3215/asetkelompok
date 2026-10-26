<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PenggunaController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna/karyawan.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pengguna::with(['peran', 'kantor', 'divisi']);

        if ($request->filled('kantor_id')) {
            $query->where('kantor_id', $request->kantor_id);
        }
        if ($request->filled('peran_id')) {
            $query->where('peran_id', $request->peran_id);
        }
        if ($request->filled('peran')) {
            $peranKode = $request->peran;
            $query->whereHas('peran', function ($q) use ($peranKode) {
                $q->where('kode_peran', $peranKode);
            });
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->boolean('semua') || $request->input('per_page') == -1) {
            $daftar = $query->latest('id')->get();
        } else {
            $perPage = (int) $request->input('per_page', 15);
            $daftar = $query->latest('id')->paginate($perPage);
        }

        return response()->json([
            'status' => 'success',
            'pesan' => 'Daftar pengguna berhasil dimuat.',
            'data' => $daftar,
        ]);
    }

    /**
     * Tambah akun pengguna baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string|max:30|unique:pengguna,nip',
            'nama' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:pengguna,email',
            'kata_sandi' => 'required|string|min:6',
            'nomor_telepon' => 'nullable|string|max:30',
            'peran_id' => 'required|exists:peran,id',
            'kantor_id' => 'required|exists:kantor,id',
            'divisi_id' => 'nullable|exists:divisi,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'pesan' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $pengguna = Pengguna::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'nomor_telepon' => $request->nomor_telepon,
            'peran_id' => $request->peran_id,
            'kantor_id' => $request->kantor_id,
            'divisi_id' => $request->divisi_id,
            'status_aktif' => true,
        ]);

        AuditLogService::catat('Pengguna', 'create', "Pembuatan akun pengguna [{$pengguna->nip}] {$pengguna->nama}");

        return response()->json([
            'status' => 'success',
            'pesan' => 'Pengguna berhasil ditambahkan.',
            'data' => $pengguna->load(['peran', 'kantor', 'divisi']),
        ], 201);
    }

    /**
     * Tampilkan seluruh peran (role).
     */
    public function peran(): JsonResponse
    {
        $peran = Peran::with('hakAkses')->get();
        return response()->json(['status' => 'success', 'data' => $peran]);
    }

    /**
     * Tampilkan seluruh hak akses.
     */
    public function hakAkses(): JsonResponse
    {
        $hakAkses = HakAkses::all();
        return response()->json(['status' => 'success', 'data' => $hakAkses]);
    }
}
