<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\PenyusutanAset;
use App\Models\RekomendasiPeremajaan;
use App\Services\AuditLogService;
use App\Services\DepresiasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepresiasiController extends Controller
{
    /**
     * Tampilkan data penyusutan dan ringkasan nilai buku aset perusahaan.
     */
    public function index(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        $bulan = (int) $request->get('bulan', date('n'));

        $query = PenyusutanAset::with(['aset.kategori', 'aset.kantor'])
            ->where('periode_tahun', $tahun);

        if ($request->filled('bulan')) {
            $query->where('periode_bulan', $bulan);
        }

        $daftar = $query->latest('id')->paginate(15);

        // Ringkasan Akuntansi
        $totalNilaiPerolehan = (float) Aset::where('jenis_kepemilikan', 'milik_sendiri')->sum('nilai_perolehan');
        $totalAkumulasiPenyusutan = (float) Aset::where('jenis_kepemilikan', 'milik_sendiri')->sum('akumulasi_penyusutan');
        $totalNilaiBuku = (float) Aset::where('jenis_kepemilikan', 'milik_sendiri')->sum('nilai_buku_saat_ini');
        $bebanBulanIni = (float) PenyusutanAset::where('periode_tahun', $tahun)->where('periode_bulan', $bulan)->sum('beban_penyusutan');

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data penyusutan aset berhasil dimuat.',
            'ringkasan' => [
                'periode' => "{$tahun}-" . str_pad($bulan, 2, '0', STR_PAD_LEFT),
                'total_nilai_perolehan' => $totalNilaiPerolehan,
                'total_akumulasi_penyusutan' => $totalAkumulasiPenyusutan,
                'total_nilai_buku_saat_ini' => $totalNilaiBuku,
                'beban_penyusutan_periode_ini' => $bebanBulanIni,
            ],
            'data' => $daftar,
        ]);
    }

    /**
     * Jalankan proses batch otomatis perhitungan depresiasi garis lurus.
     */
    public function hitungBatch(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        if (!$pengguna->memilikiPeran('admin') && !$pengguna->memilikiPeran('manajer')) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak: Hanya Admin atau Manajer yang dapat menjalankan perhitungan depresiasi batch.',
            ], 403);
        }

        $tahun = (int) $request->get('tahun', date('Y'));
        $bulan = (int) $request->get('bulan', date('n'));

        $jumlahAset = DepresiasiService::hitungBatch($tahun, $bulan);

        AuditLogService::catat(
            'Depresiasi',
            'calculate',
            "Perhitungan batch penyusutan periode {$tahun}-{$bulan} terhadap {$jumlahAset} aset oleh {$pengguna->nama}"
        );

        return response()->json([
            'status' => 'success',
            'pesan' => "Perhitungan depresiasi batch periode {$tahun}-{$bulan} berhasil diselesaikan untuk {$jumlahAset} aset.",
            'jumlah_aset_dihitung' => $jumlahAset,
        ]);
    }

    /**
     * Tampilkan daftar aset yang direkomendasikan untuk peremajaan / replacement.
     */
    public function rekomendasi(Request $request): JsonResponse
    {
        $query = RekomendasiPeremajaan::with(['aset.kategori', 'aset.kantor']);

        if ($request->filled('prioritas')) {
            $query->where('prioritas_rekomendasi', $request->prioritas);
        }

        $daftar = $query->orderByRaw("FIELD(prioritas_rekomendasi, 'segera', 'tinggi', 'sedang', 'rendah')")->paginate(15);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Daftar rekomendasi peremajaan aset berhasil dimuat.',
            'data' => $daftar,
        ]);
    }
}
