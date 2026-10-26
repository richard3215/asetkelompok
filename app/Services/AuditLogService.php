<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Models\RiwayatAset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Catat log aktivitas umum sistem.
     */
    public static function catat(string $modul, string $aksi, string $deskripsi, $dataLama = null, $dataBaru = null): LogAktivitas
    {
        $pengguna = Auth::user();

        return LogAktivitas::create([
            'pengguna_id' => $pengguna ? $pengguna->id : null,
            'kantor_id' => $pengguna ? $pengguna->kantor_id : null,
            'modul' => $modul,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'data_sebelumnya' => $dataLama,
            'data_sesudahnya' => $dataBaru,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Catat mutasi atau riwayat siklus hidup aset.
     */
    public static function catatMutasiAset(
        int $asetId,
        string $jenisAktivitas,
        string $keterangan,
        ?int $kantorAsalId = null,
        ?int $kantorTujuanId = null,
        ?int $divisiAsalId = null,
        ?int $divisiTujuanId = null,
        ?string $kondisiSebelumnya = null,
        ?string $kondisiSesudahnya = null,
        ?string $statusSebelumnya = null,
        ?string $statusSesudahnya = null,
        $nilaiLama = null,
        $nilaiBaru = null
    ): RiwayatAset {
        $pengguna = Auth::user();

        return RiwayatAset::create([
            'aset_id' => $asetId,
            'pengguna_id' => $pengguna ? $pengguna->id : null,
            'jenis_aktivitas' => $jenisAktivitas,
            'kantor_asal_id' => $kantorAsalId,
            'kantor_tujuan_id' => $kantorTujuanId,
            'divisi_asal_id' => $divisiAsalId,
            'divisi_tujuan_id' => $divisiTujuanId,
            'kondisi_sebelumnya' => $kondisiSebelumnya,
            'kondisi_sesudahnya' => $kondisiSesudahnya,
            'status_sebelumnya' => $statusSebelumnya,
            'status_sesudahnya' => $statusSesudahnya,
            'keterangan' => $keterangan,
            'nilai_lama' => $nilaiLama,
            'nilai_baru' => $nilaiBaru,
            'ip_address' => Request::ip(),
        ]);
    }
}
