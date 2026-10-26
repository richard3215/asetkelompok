<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Kantor;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganisasiController extends Controller
{
    /**
     * Tampilkan seluruh kantor beserta hierarki cabang dan pembantu.
     */
    public function kantor(Request $request): JsonResponse
    {
        $kantor = Kantor::with(['cabang', 'divisi'])->whereNull('induk_kantor_id')->get();
        $semuaKantor = Kantor::orderBy('kode_kantor')->get();

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data struktur organisasi kantor berhasil dimuat.',
            'hierarki' => $kantor,
            'daftar' => $semuaKantor,
        ]);
    }

    /**
     * Tambah kantor baru.
     */
    public function storeKantor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_kantor' => 'required|string|max:30|unique:kantor,kode_kantor',
            'nama_kantor' => 'required|string|max:150',
            'jenis_kantor' => 'required|in:pusat,cabang,unit_pembantu',
            'induk_kantor_id' => 'nullable|exists:kantor,id',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'pesan' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $kantor = Kantor::create($request->all());
        AuditLogService::catat('Organisasi', 'create', "Penambahan unit kantor [{$kantor->kode_kantor}] {$kantor->nama_kantor}");

        return response()->json(['status' => 'success', 'pesan' => 'Kantor berhasil ditambahkan.', 'data' => $kantor], 201);
    }

    /**
     * Tampilkan divisi berdasarkan kantor.
     */
    public function divisi(Request $request): JsonResponse
    {
        $query = Divisi::with('kantor');
        if ($request->filled('kantor_id')) {
            $query->where('kantor_id', $request->kantor_id);
        }

        $daftar = $query->orderBy('kode_divisi')->get();

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data divisi berhasil dimuat.',
            'data' => $daftar,
        ]);
    }

    /**
     * Tambah divisi baru.
     */
    public function storeDivisi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_divisi' => 'required|string|max:30|unique:divisi,kode_divisi',
            'nama_divisi' => 'required|string|max:150',
            'kantor_id' => 'required|exists:kantor,id',
            'induk_divisi_id' => 'nullable|exists:divisi,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'pesan' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $divisi = Divisi::create($request->all());
        AuditLogService::catat('Organisasi', 'create', "Penambahan divisi [{$divisi->kode_divisi}] {$divisi->nama_divisi}");

        return response()->json(['status' => 'success', 'pesan' => 'Divisi berhasil ditambahkan.', 'data' => $divisi], 201);
    }
}
