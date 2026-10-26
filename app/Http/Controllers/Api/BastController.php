<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\BeritaAcaraSerahTerima;
use App\Models\DetailBast;
use App\Models\Kantor;
use App\Models\Notifikasi;
use App\Models\PengaturanSistem;
use App\Models\Pengguna;
use App\Models\TandaTanganElektronik;
use App\Services\AuditLogService;
use App\Services\NomorIndexService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BastController extends Controller
{
    /**
     * Tampilkan daftar dokumen Berita Acara Serah Terima (BAST).
     */
    public function index(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $query = BeritaAcaraSerahTerima::with(['pihakPenyerah', 'pihakPenerima', 'disetujuiManajer', 'kantor', 'detail.aset', 'tandaTangan']);

        if (!$pengguna->memilikiPeran('admin') && $pengguna->kantor && $pengguna->kantor->jenis_kantor !== 'pusat') {
            $query->where('kantor_id', $pengguna->kantor_id);
        }

        if ($request->filled('status')) {
            $query->where('status_bast', $request->status);
        }

        $daftar = $query->latest('id')->paginate(15);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Daftar BAST berhasil dimuat.',
            'data' => $daftar,
        ]);
    }

    /**
     * Buat Draft Dokumen BAST baru.
     */
    public function store(Request $request): JsonResponse
    {
        $pengguna = $request->user();

        $validator = Validator::make($request->all(), [
            'kantor_id' => 'required|exists:kantor,id',
            'divisi_id' => 'nullable|exists:divisi,id',
            'jenis_transaksi' => 'required|in:penyerahan_karyawan,pengembalian,pemindahan,penggantian,penarikan,pengadaan_baru',
            'pihak_penyerah_id' => 'required|exists:pengguna,id',
            'pihak_penerima_id' => 'required|exists:pengguna,id',
            'tanggal_bast' => 'required|date',
            'catatan' => 'nullable|string',
            'aset_items' => 'required|array|min:1',
            'aset_items.*.aset_id' => 'required|exists:aset,id',
            'aset_items.*.kondisi' => 'required|string',
            'aset_items.*.keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi pembuatan BAST gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kantor = Kantor::findOrFail($request->kantor_id);

        $bast = DB::transaction(function () use ($request, $kantor, $pengguna) {
            $nomorBast = NomorIndexService::buatNomorDokumen('BAST', $kantor->kode_kantor);

            $b = BeritaAcaraSerahTerima::create([
                'nomor_bast' => $nomorBast,
                'tanggal_bast' => $request->tanggal_bast,
                'jenis_transaksi' => $request->jenis_transaksi,
                'kantor_id' => $kantor->id,
                'divisi_id' => $request->divisi_id,
                'pihak_penyerah_id' => $request->pihak_penyerah_id,
                'pihak_penerima_id' => $request->pihak_penerima_id,
                'status_bast' => 'menunggu_tanda_tangan',
                'catatan' => $request->catatan,
            ]);

            foreach ($request->aset_items as $item) {
                DetailBast::create([
                    'berita_acara_serah_terima_id' => $b->id,
                    'aset_id' => $item['aset_id'],
                    'kondisi_saat_serah' => $item['kondisi'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            AuditLogService::catat('BAST', 'create', "Pembuatan draft BAST {$b->nomor_bast} oleh {$pengguna->nama}");

            return $b;
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'Draft BAST berhasil diterbitkan.',
            'data' => $bast->load(['pihakPenyerah', 'pihakPenerima', 'detail.aset']),
        ], 201);
    }

    /**
     * Rekam Tanda Tangan Elektronik pihak penyerah/penerima pada dokumen BAST.
     */
    public function tandaTangan(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $bast = BeritaAcaraSerahTerima::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'peran_penanda_tangan' => 'required|in:penyerah,penerima,manajer',
            'gambar_tanda_tangan_base64' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi tanda tangan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Hitung hash integritas dokumen
        $payloadHash = hash('sha256', $bast->nomor_bast . '|' . $pengguna->id . '|' . now()->toIso8601String());

        $ttd = TandaTanganElektronik::updateOrCreate(
            [
                'entitas_tipe' => 'bast',
                'entitas_id' => $bast->id,
                'peran_penanda_tangan' => $request->peran_penanda_tangan,
            ],
            [
                'penanda_tangan_id' => $pengguna->id,
                'file_tanda_tangan' => $request->gambar_tanda_tangan_base64,
                'hash_integritas_dokumen' => $payloadHash,
                'waktu_tanda_tangan' => now(),
                'ip_address' => $request->ip(),
            ]
        );

        // Jika kedua pihak sudah menandatangani, status menjadi menunggu persetujuan manajer
        $jumlahTtd = TandaTanganElektronik::where('entitas_tipe', 'bast')->where('entitas_id', $bast->id)->count();
        if ($jumlahTtd >= 2 && $bast->status_bast === 'menunggu_tanda_tangan') {
            $bast->update(['status_bast' => 'menunggu_persetujuan_manajer']);
        }

        AuditLogService::catat('BAST', 'sign', "Penandatanganan dokumen BAST {$bast->nomor_bast} oleh {$pengguna->nama}");

        return response()->json([
            'status' => 'success',
            'pesan' => 'Tanda tangan elektronik berhasil direkam secara aman.',
            'data' => $ttd,
        ]);
    }

    /**
     * Persetujuan & Pengesahan BAST oleh Manajer.
     */
    public function sahkan(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $bast = BeritaAcaraSerahTerima::with('detail.aset')->findOrFail($id);

        if (!$pengguna->memilikiPeran('manajer') && !$pengguna->memilikiPeran('admin')) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak: Hanya Manajer atau Admin yang berhak mengesahkan BAST.',
            ], 403);
        }

        DB::transaction(function () use ($bast, $pengguna) {
            $bast->status_bast = 'selesai_disahkan';
            $bast->disetujui_manajer_id = $pengguna->id;
            $bast->save();

            // Mutasikan aset ke penanggung jawab baru jika jenis penyerahan
            if ($bast->jenis_transaksi === 'penyerahan_karyawan') {
                foreach ($bast->detail as $item) {
                    if ($item->aset) {
                        $item->aset->update([
                            'penanggung_jawab_id' => $bast->pihak_penerima_id,
                            'status_operasional' => 'aktif',
                        ]);

                        AuditLogService::catatMutasiAset(
                            $item->aset_id,
                            'serah_terima',
                            "Serah terima resmi ke penanggung jawab NIP {$bast->pihakPenerima->nip}",
                            $bast->kantor_id,
                            $bast->kantor_id
                        );
                    }
                }
            }

            AuditLogService::catat('BAST', 'approve', "Pengesahan resmi BAST {$bast->nomor_bast} oleh Manajer {$pengguna->nama}");
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'BAST berhasil disahkan dan berlaku resmi.',
            'data' => $bast->fresh(['pihakPenyerah', 'pihakPenerima', 'disetujuiManajer', 'tandaTangan']),
        ]);
    }

    /**
     * Cetak atau Ekspor Dokumen Resmi BAST dalam format PDF.
     */
    public function cetakPdf($id)
    {
        $bast = BeritaAcaraSerahTerima::with([
            'pihakPenyerah',
            'pihakPenerima',
            'disetujuiManajer',
            'kantor',
            'divisi',
            'detail.aset.kategori',
            'tandaTangan.penandaTangan',
        ])->findOrFail($id);

        $namaPerusahaan = PengaturanSistem::ambil('nama_perusahaan', 'PT ASET NUSANTARA SEJAHTERA');
        $alamatPerusahaan = PengaturanSistem::ambil('alamat_perusahaan', 'Gedung Menara Mandiri Lt. 28, Jakarta');
        $teleponPerusahaan = PengaturanSistem::ambil('telepon_perusahaan', '(021) 5299-8800');

        $isDraft = $bast->status_bast !== 'selesai_disahkan';

        $pdf = Pdf::loadView('pdf.bast', compact(
            'bast',
            'namaPerusahaan',
            'alamatPerusahaan',
            'teleponPerusahaan',
            'isDraft'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("BAST-{$bast->nomor_bast}.pdf");
    }
}
