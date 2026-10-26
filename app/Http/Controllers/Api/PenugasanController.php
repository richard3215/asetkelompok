<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\DetailPenugasan;
use App\Models\Kantor;
use App\Models\LampiranKegiatan;
use App\Models\Notifikasi;
use App\Models\PenugasanStaf;
use App\Services\AuditLogService;
use App\Services\NomorIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenugasanController extends Controller
{
    /**
     * Tampilkan daftar penugasan staf.
     */
    public function index(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $query = PenugasanStaf::with(['pemberiTugas', 'stafPelaksana', 'kantor', 'detail.aset', 'lampiran']);

        // Staf hanya melihat tugas yang diberikan kepada dirinya
        if ($pengguna->memilikiPeran('staf')) {
            $query->where('staf_pelaksana_id', $pengguna->id);
        } elseif (!$pengguna->memilikiPeran('admin') && $pengguna->kantor && $pengguna->kantor->jenis_kantor !== 'pusat') {
            $query->where('kantor_id', $pengguna->kantor_id);
        }

        if ($request->filled('status')) {
            $query->where('status_penugasan', $request->status);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis_penugasan', $request->jenis);
        }

        $daftar = $query->latest('id')->paginate(15);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Daftar penugasan berhasil diambil.',
            'data' => $daftar,
        ]);
    }

    /**
     * Buat penugasan baru dari Manajer kepada Staf.
     */
    public function store(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        $validator = Validator::make($request->all(), [
            'staf_pelaksana_id' => 'required|exists:pengguna,id',
            'kantor_id' => 'required|exists:kantor,id',
            'jenis_penugasan' => 'required|in:inventarisasi,pemeriksaan_kondisi,pemeliharaan,penggantian,penarikan,pengembalian,pemindahan,pelabelan_qr',
            'prioritas' => 'required|in:rendah,sedang,tinggi,darurat',
            'tanggal_penugasan' => 'required|date',
            'tenggat_waktu' => 'required|date|after_or_equal:tanggal_penugasan',
            'instruksi' => 'required|string',
            'aset_ids' => 'required|array|min:1',
            'aset_ids.*' => 'exists:aset,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi pembuatan penugasan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kantor = Kantor::findOrFail($request->kantor_id);

        $penugasan = DB::transaction(function () use ($request, $pengguna, $kantor) {
            $nomorTugas = NomorIndexService::buatNomorDokumen('TSK', $kantor->kode_kantor);

            $tugas = PenugasanStaf::create([
                'nomor_penugasan' => $nomorTugas,
                'pemberi_tugas_id' => $pengguna->id,
                'staf_pelaksana_id' => $request->staf_pelaksana_id,
                'kantor_id' => $kantor->id,
                'jenis_penugasan' => $request->jenis_penugasan,
                'prioritas' => $request->prioritas,
                'tanggal_penugasan' => $request->tanggal_penugasan,
                'tenggat_waktu' => $request->tenggat_waktu,
                'instruksi' => $request->instruksi,
                'status_penugasan' => 'diberikan',
            ]);

            foreach ($request->aset_ids as $asetId) {
                $itemAset = Aset::find($asetId);
                DetailPenugasan::create([
                    'penugasan_staf_id' => $tugas->id,
                    'aset_id' => $asetId,
                    'kondisi_sebelum' => $itemAset ? $itemAset->kondisi : 'baik',
                    'status_pengerjaan' => 'belum',
                ]);
            }

            // Kirim notifikasi ke staf pelaksana
            Notifikasi::create([
                'pengguna_id' => $request->staf_pelaksana_id,
                'judul' => 'Tugas Baru Diterima',
                'pesan' => "Anda menerima tugas [{$tugas->nomor_penugasan}] jenis: {$tugas->jenis_penugasan}. Tenggat: {$tugas->tenggat_waktu->toDateString()}.",
                'tipe_notifikasi' => 'tugas_baru',
                'referensi_tipe' => 'penugasan',
                'referensi_id' => $tugas->id,
            ]);

            AuditLogService::catat('Penugasan', 'create', "Manajer {$pengguna->nama} membuat penugasan {$tugas->nomor_penugasan}");

            return $tugas;
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'Penugasan berhasil dibuat dan diteruskan kepada staf pelaksana.',
            'data' => $penugasan->load(['pemberiTugas', 'stafPelaksana', 'detail.aset']),
        ], 201);
    }

    /**
     * Staf melaksanakan penugasan dan mengunggah foto kegiatan.
     */
    public function kerjakan(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $tugas = PenugasanStaf::with('detail')->findOrFail($id);

        if ($tugas->staf_pelaksana_id !== $pengguna->id && !$pengguna->memilikiPeran('admin')) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak. Tugas ini tidak ditujukan untuk akun Anda.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'catatan_pelaksanaan' => 'required|string',
            'detail_aset' => 'nullable|array',
            'detail_aset.*.aset_id' => 'required|exists:aset,id',
            'detail_aset.*.kondisi_sesudah' => 'required|in:sangat_baik,baik,rusak_ringan,rusak_berat,tidak_ekonomis',
            'detail_aset.*.lokasi_ditemukan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi pengerjaan tugas gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $tugas, $pengguna) {
            $tugas->catatan_pelaksanaan = $request->catatan_pelaksanaan;
            $tugas->status_penugasan = 'sedang_dikerjakan';
            $tugas->save();

            if ($request->has('detail_aset')) {
                foreach ($request->detail_aset as $d) {
                    DetailPenugasan::where('penugasan_staf_id', $tugas->id)
                        ->where('aset_id', $d['aset_id'])
                        ->update([
                            'kondisi_sesudah' => $d['kondisi_sesudah'],
                            'lokasi_ditemukan' => $d['lokasi_ditemukan'] ?? null,
                            'status_pengerjaan' => 'selesai',
                        ]);
                }
            }

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $path = $file->store('bukti_tugas', 'public');

                LampiranKegiatan::create([
                    'entitas_tipe' => 'penugasan',
                    'entitas_id' => $tugas->id,
                    'jenis_lampiran' => 'bukti_kegiatan',
                    'file_path' => $path,
                    'nama_file_asli' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'ukuran_file' => $file->getSize(),
                    'diunggah_oleh_id' => $pengguna->id,
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data pelaksanaan tugas berhasil disimpan.',
            'data' => $tugas->fresh(['detail.aset', 'lampiran']),
        ]);
    }

    /**
     * Staf mengirimkan tugas untuk validasi Manajer.
     */
    public function kirimValidasi(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $tugas = PenugasanStaf::findOrFail($id);

        if ($tugas->staf_pelaksana_id !== $pengguna->id && !$pengguna->memilikiPeran('admin')) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak.',
            ], 403);
        }

        // Cek bukti foto
        $adaLampiran = LampiranKegiatan::where('entitas_tipe', 'penugasan')->where('entitas_id', $tugas->id)->exists();
        if (!$adaLampiran) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Wajib mengunggah minimal 1 foto bukti kegiatan sebelum mengajukan validasi.',
            ], 422);
        }

        $tugas->update([
            'status_penugasan' => 'menunggu_validasi',
            'tanggal_selesai_pelaksanaan' => now(),
        ]);

        Notifikasi::create([
            'pengguna_id' => $tugas->pemberi_tugas_id,
            'judul' => 'Pekerjaan Menunggu Validasi',
            'pesan' => "Staf {$pengguna->nama} telah menyelesaikan tugas [{$tugas->nomor_penugasan}] dan menunggu validasi Anda.",
            'tipe_notifikasi' => 'validasi_pekerjaan',
            'referensi_tipe' => 'penugasan',
            'referensi_id' => $tugas->id,
        ]);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Pekerjaan berhasil diajukan untuk validasi Manajer.',
            'data' => $tugas,
        ]);
    }

    /**
     * Validasi hasil pekerjaan staf oleh Manajer / Atasan (Backend Enforcement).
     */
    public function validasi(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $tugas = PenugasanStaf::with('detail.aset')->findOrFail($id);

        // Anti self-approval: Staf tidak boleh memvalidasi pekerjaannya sendiri
        if ($tugas->staf_pelaksana_id === $pengguna->id && !$pengguna->memilikiPeran('admin')) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Pelanggaran otorisasi: Staf tidak diizinkan memvalidasi pekerjaannya sendiri.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'keputusan' => 'required|in:setujui,revisi',
            'catatan' => 'required_if:keputusan,revisi|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $tugas, $pengguna) {
            if ($request->keputusan === 'setujui') {
                $tugas->status_penugasan = 'selesai';
                $tugas->save();

                // Perbarui status dan kondisi aset di database
                foreach ($tugas->detail as $dt) {
                    if ($dt->aset && $dt->kondisi_sesudah) {
                        $dt->aset->update([
                            'kondisi' => $dt->kondisi_sesudah,
                            'status_inventaris' => 'ditemukan',
                        ]);

                        AuditLogService::catatMutasiAset(
                            $dt->aset_id,
                            'penugasan_selesai',
                            "Penyelesaian penugasan [{$tugas->nomor_penugasan}]. Kondisi dimutakhirkan menjadi {$dt->kondisi_sesudah}.",
                            $tugas->kantor_id,
                            $tugas->kantor_id,
                            null,
                            null,
                            $dt->kondisi_sebelum,
                            $dt->kondisi_sesudah
                        );
                    }
                }

                Notifikasi::create([
                    'pengguna_id' => $tugas->staf_pelaksana_id,
                    'judul' => 'Tugas Disetujui',
                    'pesan' => "Pekerjaan penugasan [{$tugas->nomor_penugasan}] telah diverifikasi dan disetujui Manajer.",
                    'tipe_notifikasi' => 'tugas_selesai',
                    'referensi_tipe' => 'penugasan',
                    'referensi_id' => $tugas->id,
                ]);
            } else {
                $tugas->status_penugasan = 'revisi';
                $tugas->alasan_revisi = $request->catatan;
                $tugas->save();

                Notifikasi::create([
                    'pengguna_id' => $tugas->staf_pelaksana_id,
                    'judul' => 'Tugas Dikembalikan untuk Perbaikan',
                    'pesan' => "Tugas [{$tugas->nomor_penugasan}] perlu perbaikan. Catatan: {$request->catatan}",
                    'tipe_notifikasi' => 'tugas_revisi',
                    'referensi_tipe' => 'penugasan',
                    'referensi_id' => $tugas->id,
                ]);
            }

            AuditLogService::catat('Penugasan', 'validasi', "Keputusan validasi [{$request->keputusan}] pada penugasan {$tugas->nomor_penugasan}");
        });

        return response()->json([
            'status' => 'success',
            'pesan' => $request->keputusan === 'setujui' ? 'Pekerjaan berhasil disetujui.' : 'Pekerjaan dikembalikan ke staf untuk perbaikan.',
            'data' => $tugas->fresh(),
        ]);
    }
}
