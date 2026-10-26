<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Kantor;
use App\Models\KategoriAset;
use App\Models\PengaturanSistem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Ringkasan data untuk tab laporan di aplikasi Android.
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $kantorId = $request->kantor_id;

        $query = Aset::query();
        if ($kantorId) {
            $query->where('kantor_id', $kantorId);
        }

        $totalAset = (clone $query)->count();
        $totalMilik = (clone $query)->where('jenis_kepemilikan', 'milik_sendiri')->count();
        $totalSewa = (clone $query)->where('jenis_kepemilikan', 'sewa_vendor')->count();
        $nilaiBuku = (clone $query)->sum('nilai_buku_saat_ini');
        $nilaiPerolehan = (clone $query)->sum('nilai_perolehan');

        $kondisi = [
            'sangat_baik' => (clone $query)->where('kondisi', 'sangat_baik')->count(),
            'baik' => (clone $query)->where('kondisi', 'baik')->count(),
            'rusak_ringan' => (clone $query)->where('kondisi', 'rusak_ringan')->count(),
            'rusak_berat' => (clone $query)->where('kondisi', 'rusak_berat')->count(),
            'tidak_ekonomis' => (clone $query)->where('kondisi', 'tidak_ekonomis')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'pesan' => 'Ringkasan laporan berhasil dimuat.',
            'data' => [
                'total_aset' => $totalAset,
                'total_milik' => $totalMilik,
                'total_sewa' => $totalSewa,
                'nilai_perolehan' => (float) $nilaiPerolehan,
                'nilai_buku' => (float) $nilaiBuku,
                'kondisi' => $kondisi,
            ],
        ]);
    }

    /**
     * Ekspor Laporan Formal PDF Aset Perusahaan dengan Narasi Dinamis.
     */
    public function asetPdf(Request $request)
    {
        $pengguna = $request->user();

        $query = Aset::with(['kategori', 'kantor', 'penanggungJawab', 'vendor']);

        // Filter
        if ($request->filled('kantor_id')) {
            $query->where('kantor_id', $request->kantor_id);
            $kantorFilter = Kantor::find($request->kantor_id)?->nama_kantor;
        } else {
            $kantorFilter = 'Seluruh Unit Kerja / Nasional';
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_aset_id', $request->kategori_id);
            $kategoriFilter = KategoriAset::find($request->kategori_id)?->nama_kategori;
        } else {
            $kategoriFilter = 'Semua Kategori';
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
            $kondisiFilter = ucfirst(str_replace('_', ' ', $request->kondisi));
        } else {
            $kondisiFilter = 'Semua Kondisi';
        }

        $daftarAset = $query->orderBy('nomor_index')->get();

        // Data Agregasi untuk Narasi Eksekutif Dinamis
        $totalAset = $daftarAset->count();
        $milikCount = $daftarAset->where('jenis_kepemilikan', 'milik_sendiri')->count();
        $sewaCount = $daftarAset->where('jenis_kepemilikan', 'sewa_vendor')->count();
        $totalNilaiPerolehan = $daftarAset->where('jenis_kepemilikan', 'milik_sendiri')->sum('nilai_perolehan');
        $totalNilaiBuku = $daftarAset->where('jenis_kepemilikan', 'milik_sendiri')->sum('nilai_buku_saat_ini');
        $totalAkumulasi = $daftarAset->where('jenis_kepemilikan', 'milik_sendiri')->sum('akumulasi_penyusutan');

        $baikCount = $daftarAset->whereIn('kondisi', ['sangat_baik', 'baik'])->count();
        $rusakRinganCount = $daftarAset->where('kondisi', 'rusak_ringan')->count();
        $rusakBeratCount = $daftarAset->whereIn('kondisi', ['rusak_berat', 'tidak_ekonomis'])->count();

        $persenPrima = $totalAset > 0 ? round(($baikCount / $totalAset) * 100, 1) : 0;

        // Narasi Eksekutif Dinamis berdasarkan data nyata
        $narasiEksekutif = "Berdasarkan inventarisasi data per tanggal " . now()->isoFormat('D MMMM Y') . ", tercatat sebanyak {$totalAset} unit aset yang berada di bawah pengawasan {$kantorFilter}. "
            . "Dari total tersebut, {$milikCount} unit ({$persenPrima}%) merupakan aset milik perusahaan dengan total nilai perolehan sebesar Rp " . number_format($totalNilaiPerolehan, 0, ',', '.') . " dan nilai buku saat ini sebesar Rp " . number_format($totalNilaiBuku, 0, ',', '.') . ". "
            . "Sebanyak {$sewaCount} unit merupakan aset yang dikelola melalui kontrak kemitraan sewa vendor. "
            . "Kondisi operasional aset saat ini menunjukkan {$baikCount} unit dalam kondisi prima/layak operasi, sementara {$rusakRinganCount} unit memerlukan pemeliharaan rutin, dan {$rusakBeratCount} unit memerlukan tindakan perbaikan berat atau peremajaan.";

        $nomorLaporan = 'LAP/AST/' . date('Ym') . '/' . str_pad(rand(10, 99), 4, '0', STR_PAD_LEFT);
        $namaPerusahaan = PengaturanSistem::ambil('nama_perusahaan', 'PT ASET NUSANTARA SEJAHTERA');
        $alamatPerusahaan = PengaturanSistem::ambil('alamat_perusahaan', 'Gedung Menara Mandiri Lt. 28, Jakarta');
        $teleponPerusahaan = PengaturanSistem::ambil('telepon_perusahaan', '(021) 5299-8800');

        $pdf = Pdf::loadView('pdf.laporan_aset', compact(
            'daftarAset',
            'nomorLaporan',
            'namaPerusahaan',
            'alamatPerusahaan',
            'teleponPerusahaan',
            'kantorFilter',
            'kategoriFilter',
            'kondisiFilter',
            'totalAset',
            'milikCount',
            'sewaCount',
            'totalNilaiPerolehan',
            'totalNilaiBuku',
            'totalAkumulasi',
            'narasiEksekutif',
            'pengguna'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("Laporan-Aset-{$nomorLaporan}.pdf");
    }
}
