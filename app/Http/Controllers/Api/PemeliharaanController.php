<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\JadwalPemeliharaan;
use App\Models\PemeliharaanAset;
use App\Services\AuditLogService;
use App\Services\NomorIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PemeliharaanController extends Controller
{
    /**
     * Tampilkan jadwal pemeliharaan berkala.
     */
    public function jadwal(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $query = JadwalPemeliharaan::with(['aset.kategori', 'kantor']);

        if (!$pengguna->memilikiPeran('admin') && $pengguna->kantor && $pengguna->kantor->jenis_kantor !== 'pusat') {
            $query->where('kantor_id', $pengguna->kantor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $daftar = $query->orderBy('tanggal_berikutnya', 'asc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Jadwal pemeliharaan berhasil diambil.',
            'data' => $daftar,
        ]);
    }

    /**
     * Tampilkan riwayat tindakan pemeliharaan yang telah dicatat.
     */
    public function riwayat(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $query = PemeliharaanAset::with(['aset', 'teknisi', 'vendor', 'jadwal']);

        if ($request->filled('aset_id')) {
            $query->where('aset_id', $request->aset_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $daftar = $query->latest('id')->paginate(15);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Riwayat pemeliharaan berhasil diambil.',
            'data' => $daftar,
        ]);
    }

    /**
     * Catat pengerjaan pemeliharaan / servis baru.
     */
    public function store(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        $validator = Validator::make($request->all(), [
            'aset_id' => 'required|exists:aset,id',
            'jadwal_pemeliharaan_id' => 'nullable|exists:jadwal_pemeliharaan,id',
            'vendor_id' => 'nullable|exists:vendor,id',
            'tanggal_mulai' => 'required|date',
            'deskripsi_kerusakan' => 'required|string',
            'tindakan_perbaikan' => 'nullable|string',
            'biaya_realisasi' => 'nullable|numeric|min:0',
            'status' => 'required|in:dalam_pengerjaan,menunggu_validasi,disetujui',
            'foto_sebelum' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'foto_sesudah' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi data pemeliharaan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $aset = Aset::findOrFail($request->aset_id);

        $fotoSebelumPath = null;
        if ($request->hasFile('foto_sebelum')) {
            $fotoSebelumPath = $request->file('foto_sebelum')->store('pemeliharaan', 'public');
        }

        $fotoSesudahPath = null;
        if ($request->hasFile('foto_sesudah')) {
            $fotoSesudahPath = $request->file('foto_sesudah')->store('pemeliharaan', 'public');
        }

        $pemeliharaan = DB::transaction(function () use ($request, $aset, $pengguna, $fotoSebelumPath, $fotoSesudahPath) {
            $nomorMnt = NomorIndexService::buatNomorDokumen('MNT', $aset->kantor->kode_kantor ?? '17000');

            $mnt = PemeliharaanAset::create([
                'nomor_pemeliharaan' => $nomorMnt,
                'jadwal_pemeliharaan_id' => $request->jadwal_pemeliharaan_id,
                'aset_id' => $aset->id,
                'pengguna_teknisi_id' => $pengguna->id,
                'vendor_id' => $request->vendor_id,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->status === 'disetujui' ? now()->toDateString() : null,
                'biaya_realisasi' => $request->biaya_realisasi ?? 0,
                'deskripsi_kerusakan' => $request->deskripsi_kerusakan,
                'tindakan_perbaikan' => $request->tindakan_perbaikan,
                'status' => $request->status,
                'foto_sebelum' => $fotoSebelumPath,
                'foto_sesudah' => $fotoSesudahPath,
            ]);

            // Ubah status operasional aset
            if ($request->status === 'dalam_pengerjaan') {
                $aset->update(['status_operasional' => 'dalam_pemeliharaan']);
            } elseif ($request->status === 'disetujui') {
                $aset->update(['status_operasional' => 'aktif', 'kondisi' => 'baik']);
            }

            AuditLogService::catatMutasiAset(
                $aset->id,
                'pemeliharaan',
                "Tindakan pemeliharaan [{$mnt->nomor_pemeliharaan}]: {$mnt->deskripsi_kerusakan}",
                $aset->kantor_id,
                $aset->kantor_id
            );

            return $mnt;
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'Pencatatan pemeliharaan berhasil disimpan.',
            'data' => $pemeliharaan->load(['aset', 'teknisi']),
        ], 201);
    }
}
