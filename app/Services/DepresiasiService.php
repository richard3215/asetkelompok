<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\PenyusutanAset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DepresiasiService
{
    /**
     * Hitung penyusutan bulanan untuk satu aset.
     */
    public static function hitungBulanan(Aset $aset, int $tahun, int $bulan): ?PenyusutanAset
    {
        // Aset sewa atau non-penyusutan tidak didepresiasi
        if ($aset->jenis_kepemilikan === 'sewa_vendor' || $aset->metode_penyusutan === 'tanpa_penyusutan') {
            return null;
        }

        return DB::transaction(function () use ($aset, $tahun, $bulan) {
            $nilaiPerolehan = (float) $aset->nilai_perolehan;
            $nilaiResidu = (float) $aset->nilai_residu;
            $masaManfaatBulan = max(1, (int) $aset->masa_manfaat_bulan);

            $nilaiYangDisusutkan = max(0, $nilaiPerolehan - $nilaiResidu);
            $bebanPerBulan = round($nilaiYangDisusutkan / $masaManfaatBulan, 2);

            // Akumulasi sebelum periode ini
            $akumulasiSebelumnya = (float) PenyusutanAset::where('aset_id', $aset->id)
                ->where(function ($q) use ($tahun, $bulan) {
                    $q->where('periode_tahun', '<', $tahun)
                      ->orWhere(function ($q2) use ($tahun, $bulan) {
                          $q2->where('periode_tahun', $tahun)
                             ->where('periode_bulan', '<', $bulan);
                      });
                })->sum('beban_penyusutan');

            $sisaBisaDisusutkan = max(0, $nilaiYangDisusutkan - $akumulasiSebelumnya);
            $bebanFinal = min($bebanPerBulan, $sisaBisaDisusutkan);

            $akumulasiTotal = $akumulasiSebelumnya + $bebanFinal;
            $nilaiBuku = max($nilaiResidu, $nilaiPerolehan - $akumulasiTotal);

            $penyusutan = PenyusutanAset::updateOrCreate(
                [
                    'aset_id' => $aset->id,
                    'periode_tahun' => $tahun,
                    'periode_bulan' => $bulan,
                ],
                [
                    'nilai_perolehan' => $nilaiPerolehan,
                    'beban_penyusutan' => $bebanFinal,
                    'akumulasi_penyusutan' => $akumulasiTotal,
                    'nilai_buku' => $nilaiBuku,
                    'metode_penyusutan' => $aset->metode_penyusutan,
                    'dihitung_otomatis' => true,
                    'tanggal_perhitungan' => Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                ]
            );

            // Update nilai buku aset saat ini
            $aset->update([
                'nilai_buku_saat_ini' => $nilaiBuku,
                'akumulasi_penyusutan' => $akumulasiTotal,
            ]);

            return $penyusutan;
        });
    }

    /**
     * Hitung penyusutan batch untuk seluruh aset milik perusahaan yang aktif.
     */
    public static function hitungBatch(int $tahun, int $bulan): int
    {
        $daftarAset = Aset::where('jenis_kepemilikan', 'milik_sendiri')
            ->whereIn('status_operasional', ['aktif', 'terdaftar', 'dalam_pemeliharaan'])
            ->get();

        $jumlah = 0;
        foreach ($daftarAset as $aset) {
            self::hitungBulanan($aset, $tahun, $bulan);
            $jumlah++;
        }

        return $jumlah;
    }
}
