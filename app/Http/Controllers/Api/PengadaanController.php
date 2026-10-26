<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPengajuanPengadaan;
use App\Models\Kantor;
use App\Models\Notifikasi;
use App\Models\PengajuanPengadaan;
use App\Models\PengaturanSistem;
use App\Models\Pengguna;
use App\Models\Persetujuan;
use App\Models\RiwayatPersetujuan;
use App\Services\AuditLogService;
use App\Services\NomorIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengadaanController extends Controller
{
    /**
     * Tampilkan daftar pengajuan pengadaan.
     */
    public function index(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $query = PengajuanPengadaan::with(['pengaju', 'kantor', 'divisi', 'detail.kategori', 'persetujuan.penyetuju']);

        if (!$pengguna->memilikiPeran('admin') && $pengguna->kantor && $pengguna->kantor->jenis_kantor !== 'pusat') {
            $query->where('kantor_id', $pengguna->kantor_id);
        }

        if ($request->filled('status')) {
            $query->where('status_pengadaan', $request->status);
        }

        $daftar = $query->latest('id')->paginate(15);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Daftar pengajuan pengadaan berhasil diambil.',
            'data' => $daftar,
        ]);
    }

    /**
     * Ajukan pengadaan baru oleh Manajer.
     */
    public function store(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        $validator = Validator::make($request->all(), [
            'kantor_id' => 'required|exists:kantor,id',
            'divisi_id' => 'nullable|exists:divisi,id',
            'judul_pengadaan' => 'required|string|max:200',
            'deskripsi_kebutuhan' => 'required|string',
            'alasan_pengadaan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.kategori_aset_id' => 'required|exists:kategori_aset,id',
            'items.*.nama_barang' => 'required|string|max:150',
            'items.*.spesifikasi' => 'nullable|string',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.estimasi_harga_satuan' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi pengajuan pengadaan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kantor = Kantor::findOrFail($request->kantor_id);

        $pengadaan = DB::transaction(function () use ($request, $pengguna, $kantor) {
            $nomorPengadaan = NomorIndexService::buatNomorDokumen('PRC', $kantor->kode_kantor);

            $totalBiaya = 0;
            foreach ($request->items as $it) {
                $totalBiaya += ($it['jumlah'] * $it['estimasi_harga_satuan']);
            }

            $batasApproval = (float) PengaturanSistem::ambil('batas_nominal_approval_kepala_kantor', 10000000);
            $statusAwal = 'menunggu_persetujuan_kepala_kantor';

            $model = PengajuanPengadaan::create([
                'nomor_pengadaan' => $nomorPengadaan,
                'kantor_id' => $kantor->id,
                'divisi_id' => $request->divisi_id,
                'pengaju_id' => $pengguna->id,
                'judul_pengadaan' => $request->judul_pengadaan,
                'deskripsi_kebutuhan' => $request->deskripsi_kebutuhan,
                'alasan_pengadaan' => $request->alasan_pengadaan,
                'estimasi_total_biaya' => $totalBiaya,
                'status_pengadaan' => $statusAwal,
                'tanggal_pengajuan' => now()->toDateString(),
            ]);

            foreach ($request->items as $it) {
                DetailPengajuanPengadaan::create([
                    'pengajuan_pengadaan_id' => $model->id,
                    'kategori_aset_id' => $it['kategori_aset_id'],
                    'nama_barang' => $it['nama_barang'],
                    'spesifikasi' => $it['spesifikasi'] ?? null,
                    'jumlah' => $it['jumlah'],
                    'estimasi_harga_satuan' => $it['estimasi_harga_satuan'],
                    'estimasi_subtotal' => $it['jumlah'] * $it['estimasi_harga_satuan'],
                ]);
            }

            // Cari Kepala Kantor berwenang
            $kepalaKantor = Pengguna::where('kantor_id', $kantor->id)
                ->whereHas('peran', fn($q) => $q->where('kode_peran', 'kepala_kantor'))
                ->first() ?: Pengguna::whereHas('peran', fn($q) => $q->where('kode_peran', 'kepala_kantor'))->first();

            if ($kepalaKantor) {
                $persetujuan = Persetujuan::create([
                    'nomor_dokumen_referensi' => $model->nomor_pengadaan,
                    'tipe_dokumen' => 'pengadaan',
                    'dokumen_id' => $model->id,
                    'pemohon_id' => $pengguna->id,
                    'penyetuju_id' => $kepalaKantor->id,
                    'level_persetujuan' => 2,
                    'status_keputusan' => 'menunggu',
                    'komentar' => 'Pengajuan menunggu telaah anggaran Kepala Kantor.',
                ]);

                RiwayatPersetujuan::create([
                    'persetujuan_id' => $persetujuan->id,
                    'penyetuju_id' => $pengguna->id,
                    'aksi' => 'diajukan',
                    'catatan' => 'Manajer mengajukan permohonan pengadaan.',
                    'waktu_aksi' => now(),
                    'ip_address' => request()->ip(),
                ]);

                Notifikasi::create([
                    'pengguna_id' => $kepalaKantor->id,
                    'judul' => 'Pengajuan Pengadaan Baru',
                    'pesan' => "Pengajuan [{$model->nomor_pengadaan}] {$model->judul_pengadaan} (Est: Rp " . number_format($totalBiaya, 0, ',', '.') . ") menunggu persetujuan Anda.",
                    'tipe_notifikasi' => 'persetujuan_menunggu',
                    'referensi_tipe' => 'pengadaan',
                    'referensi_id' => $model->id,
                ]);
            }

            AuditLogService::catat('Pengadaan', 'create', "Pengajuan pengadaan {$model->nomor_pengadaan} senilai Rp " . number_format($totalBiaya, 0, ',', '.'));

            return $model;
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'Pengajuan pengadaan berhasil dibuat dan diteruskan ke Kepala Kantor.',
            'data' => $pengadaan->load(['detail.kategori', 'kantor']),
        ], 201);
    }

    /**
     * Keputusan Persetujuan oleh Kepala Kantor (Disetujui, Ditolak, atau Dikembalikan).
     */
    public function putusan(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $pengadaan = PengajuanPengadaan::with('persetujuan')->findOrFail($id);

        // Hanya Kepala Kantor atau Admin yang berhak menyetujui
        if (!$pengguna->memilikiPeran('kepala_kantor') && !$pengguna->memilikiPeran('admin')) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak: Hanya Kepala Kantor atau Administrator yang memiliki kewenangan persetujuan.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'keputusan' => 'required|in:disetujui,ditolak,dikembalikan',
            'catatan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi putusan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $pengadaan, $pengguna) {
            $keputusan = $request->keputusan;

            if ($keputusan === 'disetujui') {
                $pengadaan->status_pengadaan = 'disetujui';
                $pengadaan->tanggal_disetujui = now()->toDateString();
            } elseif ($keputusan === 'ditolak') {
                $pengadaan->status_pengadaan = 'ditolak';
                $pengadaan->catatan_penolakan = $request->catatan;
            } else {
                $pengadaan->status_pengadaan = 'perlu_revisi';
                $pengadaan->catatan_penolakan = $request->catatan;
            }

            $pengadaan->save();

            // Update record persetujuan
            $persetujuan = Persetujuan::where('tipe_dokumen', 'pengadaan')
                ->where('dokumen_id', $pengadaan->id)
                ->first();

            if ($persetujuan) {
                $persetujuan->update([
                    'status_keputusan' => $keputusan,
                    'komentar' => $request->catatan,
                    'tanggal_keputusan' => now(),
                ]);

                RiwayatPersetujuan::create([
                    'persetujuan_id' => $persetujuan->id,
                    'penyetuju_id' => $pengguna->id,
                    'aksi' => $keputusan,
                    'catatan' => $request->catatan,
                    'waktu_aksi' => now(),
                    'ip_address' => request()->ip(),
                ]);
            }

            // Notifikasi ke pengaju
            Notifikasi::create([
                'pengguna_id' => $pengadaan->pengaju_id,
                'judul' => "Pengadaan {$pengadaan->nomor_pengadaan}: " . ucfirst($keputusan),
                'pesan' => "Kepala Kantor telah memutuskan: " . strtoupper($keputusan) . ". Catatan: {$request->catatan}",
                'tipe_notifikasi' => 'pengajuan_diputuskan',
                'referensi_tipe' => 'pengadaan',
                'referensi_id' => $pengadaan->id,
            ]);

            AuditLogService::catat('Persetujuan', $keputusan, "Keputusan [{$keputusan}] atas pengadaan {$pengadaan->nomor_pengadaan} oleh {$pengguna->nama}");
        });

        return response()->json([
            'status' => 'success',
            'pesan' => "Pengajuan pengadaan berhasil diputuskan: {$request->keputusan}.",
            'data' => $pengadaan->fresh(['persetujuan.riwayat']),
        ]);
    }
}
