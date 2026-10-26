<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriAset;
use App\Models\KontrakSewa;
use App\Models\Vendor;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterController extends Controller
{
    // === KATEGORI ASET ===
    public function kategori(Request $request): JsonResponse
    {
        $kategori = KategoriAset::withCount('aset')->get();
        return response()->json(['status' => 'success', 'data' => $kategori]);
    }

    public function storeKategori(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_kategori' => 'required|string|max:30|unique:kategori_aset,kode_kategori',
            'nama_kategori' => 'required|string|max:100',
            'tipe_aset' => 'required|in:perusahaan,sewa',
            'masa_manfaat_bulan' => 'required|integer|min:1',
            'persentase_residu' => 'nullable|numeric|min:0|max:100',
            'metode_penyusutan_default' => 'required|in:garis_lurus,saldo_menurun,tanpa_penyusutan',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'pesan' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $kategori = KategoriAset::create($request->all());
        AuditLogService::catat('Master', 'create', "Tambah kategori aset [{$kategori->kode_kategori}] {$kategori->nama_kategori}");

        return response()->json(['status' => 'success', 'pesan' => 'Kategori aset berhasil ditambahkan.', 'data' => $kategori], 201);
    }

    // === VENDOR ===
    public function vendor(Request $request): JsonResponse
    {
        $vendor = Vendor::withCount(['kontrakSewa', 'aset'])->get();
        return response()->json(['status' => 'success', 'data' => $vendor]);
    }

    public function storeVendor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_vendor' => 'required|string|max:30|unique:vendor,kode_vendor',
            'nama_vendor' => 'required|string|max:150',
            'kontak_person' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'pesan' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $vendor = Vendor::create($request->all());
        AuditLogService::catat('Master', 'create', "Tambah vendor [{$vendor->kode_vendor}] {$vendor->nama_vendor}");

        return response()->json(['status' => 'success', 'pesan' => 'Vendor berhasil ditambahkan.', 'data' => $vendor], 201);
    }

    // === KONTRAK SEWA ===
    public function kontrakSewa(Request $request): JsonResponse
    {
        $kontrak = KontrakSewa::with(['vendor', 'aset'])->latest('tanggal_mulai')->get();
        return response()->json(['status' => 'success', 'data' => $kontrak]);
    }
}
