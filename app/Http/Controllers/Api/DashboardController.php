<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\BeritaAcaraSerahTerima;
use App\Models\JadwalPemeliharaan;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\PemeliharaanAset;
use App\Models\Pengadaan;
use App\Models\PengajuanPengadaan;
use App\Models\Pengguna;
use App\Models\PenugasanStaf;
use App\Models\RekomendasiPeremajaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard analitik adaptif berdasarkan Peran (Role) pengguna.
     */
    public function index(Request $request): JsonResponse
    {
        $pengguna = $request->user()->load(['peran', 'kantor']);
        $kodePeran = $pengguna->peran ? $pengguna->peran->kode_peran : 'staf';
        $kantorId = $pengguna->kantor_id;
        $isPusat = $pengguna->kantor && $pengguna->kantor->jenis_kantor === 'pusat';

        $data = [
            'peran' => $kodePeran,
            'nama_pengguna' => $pengguna->nama,
            'nama_kantor' => $pengguna->kantor ? $pengguna->kantor->nama_kantor : '-',
            'notifikasi_belum_dibaca' => Notifikasi::where('pengguna_id', $pengguna->id)->where('sudah_dibaca', false)->count(),
        ];

        // 1. DASHBOARD ADMIN
        if ($kodePeran === 'admin') {
            $data['statistik'] = [
                'total_aset' => Aset::count(),
                'total_aset_milik' => Aset::where('jenis_kepemilikan', 'milik_sendiri')->count(),
                'total_aset_sewa' => Aset::where('jenis_kepemilikan', 'sewa_vendor')->count(),
                'total_nilai_perolehan' => (float) Aset::sum('nilai_perolehan'),
                'total_nilai_buku' => (float) Aset::sum('nilai_buku_saat_ini'),
                'total_akumulasi_penyusutan' => (float) Aset::sum('akumulasi_penyusutan'),
                'total_pengguna' => Pengguna::count(),
                'total_kantor' => DB::table('kantor')->count(),
            ];

            $data['distribusi_kondisi'] = [
                'sangat_baik' => Aset::where('kondisi', 'sangat_baik')->count(),
                'baik' => Aset::where('kondisi', 'baik')->count(),
                'rusak_ringan' => Aset::where('kondisi', 'rusak_ringan')->count(),
                'rusak_berat' => Aset::where('kondisi', 'rusak_berat')->count(),
                'tidak_ekonomis' => Aset::where('kondisi', 'tidak_ekonomis')->count(),
            ];

            $data['distribusi_status'] = [
                'aktif' => Aset::where('status_operasional', 'aktif')->count(),
                'dalam_pemeliharaan' => Aset::where('status_operasional', 'dalam_pemeliharaan')->count(),
                'menunggu_persetujuan' => Aset::where('status_operasional', 'menunggu_persetujuan')->count(),
                'terdaftar' => Aset::where('status_operasional', 'terdaftar')->count(),
            ];

            $data['aktivitas_terkini'] = LogAktivitas::with('pengguna')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($lg) => [
                    'id' => $lg->id,
                    'modul' => $lg->modul,
                    'aksi' => $lg->aksi,
                    'deskripsi' => $lg->deskripsi,
                    'nama_pengguna' => $lg->pengguna ? $lg->pengguna->nama : 'Sistem',
                    'waktu' => $lg->created_at->diffForHumans(),
                ]);
        }
        // 2. DASHBOARD KEPALA KANTOR
        elseif ($kodePeran === 'kepala_kantor') {
            $queryAset = Aset::query();
            if (!$isPusat) {
                $queryAset->where('kantor_id', $kantorId);
            }

            $queryPengadaan = PengajuanPengadaan::query();
            if (!$isPusat) {
                $queryPengadaan->where('kantor_id', $kantorId);
            }

            $data['statistik'] = [
                'total_aset_kantor' => (clone $queryAset)->count(),
                'nilai_aset_kantor' => (float) (clone $queryAset)->sum('nilai_buku_saat_ini'),
                'pengadaan_menunggu_persetujuan' => (clone $queryPengadaan)->where('status_pengadaan', 'menunggu_persetujuan_kepala_kantor')->count(),
                'total_pengajuan_pengadaan' => (clone $queryPengadaan)->count(),
                'rekomendasi_peremajaan_segera' => RekomendasiPeremajaan::whereHas('aset', function ($q) use ($kantorId, $isPusat) {
                    if (!$isPusat) $q->where('kantor_id', $kantorId);
                })->where('prioritas_rekomendasi', 'segera')->count(),
            ];

            $data['pengadaan_butuh_tindakan'] = (clone $queryPengadaan)
                ->with(['pengaju', 'kantor'])
                ->where('status_pengadaan', 'menunggu_persetujuan_kepala_kantor')
                ->latest()
                ->take(5)
                ->get();

            $data['distribusi_kondisi'] = [
                'sangat_baik' => (clone $queryAset)->where('kondisi', 'sangat_baik')->count(),
                'baik' => (clone $queryAset)->where('kondisi', 'baik')->count(),
                'rusak_ringan' => (clone $queryAset)->where('kondisi', 'rusak_ringan')->count(),
                'rusak_berat' => (clone $queryAset)->where('kondisi', 'rusak_berat')->count(),
            ];
        }
        // 3. DASHBOARD MANAJER
        elseif ($kodePeran === 'manajer') {
            $queryPenugasan = PenugasanStaf::query();
            $queryBast = BeritaAcaraSerahTerima::query();
            $queryAset = Aset::query();

            if (!$isPusat) {
                $queryPenugasan->where('kantor_id', $kantorId);
                $queryBast->where('kantor_id', $kantorId);
                $queryAset->where('kantor_id', $kantorId);
            }

            $data['statistik'] = [
                'tugas_menunggu_validasi' => (clone $queryPenugasan)->where('status_penugasan', 'menunggu_validasi')->count(),
                'tugas_sedang_berjalan' => (clone $queryPenugasan)->whereIn('status_penugasan', ['diberikan', 'sedang_dikerjakan'])->count(),
                'bast_menunggu_persetujuan' => (clone $queryBast)->where('status_bast', 'menunggu_persetujuan_manajer')->count(),
                'pemeliharaan_aktif' => PemeliharaanAset::where('status', 'dalam_pengerjaan')->count(),
                'total_aset_dikelola' => (clone $queryAset)->count(),
            ];

            $data['tugas_validasi'] = (clone $queryPenugasan)
                ->with(['stafPelaksana', 'kantor'])
                ->where('status_penugasan', 'menunggu_validasi')
                ->latest()
                ->take(5)
                ->get();

            $data['jadwal_mendekati_tenggat'] = JadwalPemeliharaan::with('aset')
                ->whereIn('status', ['mendekati_tenggat', 'terlambat'])
                ->latest('tanggal_berikutnya')
                ->take(5)
                ->get();
        }
        // 4. DASHBOARD STAF
        else {
            $data['statistik'] = [
                'tugas_aktif' => PenugasanStaf::where('staf_pelaksana_id', $pengguna->id)->whereIn('status_penugasan', ['diberikan', 'sedang_dikerjakan'])->count(),
                'tugas_menunggu_validasi' => PenugasanStaf::where('staf_pelaksana_id', $pengguna->id)->where('status_penugasan', 'menunggu_validasi')->count(),
                'tugas_selesai' => PenugasanStaf::where('staf_pelaksana_id', $pengguna->id)->where('status_penugasan', 'selesai')->count(),
                'pemeliharaan_ditugaskan' => PemeliharaanAset::where('pengguna_teknisi_id', $pengguna->id)->where('status', 'dalam_pengerjaan')->count(),
            ];

            $data['daftar_tugas_aktif'] = PenugasanStaf::with(['pemberiTugas', 'kantor', 'detail.aset'])
                ->where('staf_pelaksana_id', $pengguna->id)
                ->whereIn('status_penugasan', ['diberikan', 'sedang_dikerjakan', 'revisi'])
                ->orderBy('tenggat_waktu', 'asc')
                ->take(5)
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data dashboard berhasil dimuat.',
            'data' => $data,
        ]);
    }
}
