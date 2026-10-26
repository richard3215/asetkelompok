<?php

namespace App\Services;

use App\Models\PenomoranCounter;
use Illuminate\Support\Facades\DB;

class NomorIndexService
{
    /**
     * Generate Nomor Index Aset yang aman secara konkurensi dengan transaksi dan lockForUpdate.
     *
     * @param string $kodeKantor
     * @return string
     */
    public static function buatNomorIndexAset(string $kodeKantor = '17000'): string
    {
        return DB::transaction(function () use ($kodeKantor) {
            $tahun = (int) date('Y');
            $bulan = (int) date('n');
            $prefix = 'AST';

            $counter = PenomoranCounter::where('prefix', $prefix)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = PenomoranCounter::create([
                    'prefix' => $prefix,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'nomor_terakhir' => 1,
                ]);
                $urutan = 1;
            } else {
                $counter->increment('nomor_terakhir');
                $urutan = $counter->nomor_terakhir;
            }

            $bulanPadded = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $urutanPadded = str_pad($urutan, 5, '0', STR_PAD_LEFT);

            return "{$prefix}/{$kodeKantor}/{$tahun}{$bulanPadded}/{$urutanPadded}";
        });
    }

    /**
     * Generate Nomor Dokumen Transaksi Lainnya (BAST, Penugasan, Pengadaan, dll).
     */
    public static function buatNomorDokumen(string $prefix, string $kodeKantor = '17000'): string
    {
        return DB::transaction(function () use ($prefix, $kodeKantor) {
            $tahun = (int) date('Y');
            $bulan = (int) date('n');

            $counter = PenomoranCounter::where('prefix', $prefix)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = PenomoranCounter::create([
                    'prefix' => $prefix,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'nomor_terakhir' => 1,
                ]);
                $urutan = 1;
            } else {
                $counter->increment('nomor_terakhir');
                $urutan = $counter->nomor_terakhir;
            }

            $bulanPadded = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $urutanPadded = str_pad($urutan, 4, '0', STR_PAD_LEFT);

            return "{$prefix}/{$kodeKantor}/{$tahun}{$bulanPadded}/{$urutanPadded}";
        });
    }
}
