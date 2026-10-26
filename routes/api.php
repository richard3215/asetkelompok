<?php

use App\Http\Controllers\Api\AsetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BastController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepresiasiController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\OrganisasiController;
use App\Http\Controllers\Api\PemeliharaanController;
use App\Http\Controllers\Api\PengadaanController;
use App\Http\Controllers\Api\PenggunaController;
use App\Http\Controllers\Api\PenugasanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SI-ASET API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::get('/ping', function () {
    return response()->json([
        'status' => 'success',
        'aplikasi' => 'SI-ASET (Sistem Informasi Manajemen Aset Perusahaan)',
        'versi' => '1.0.0',
        'waktu' => now()->toIso8601String(),
    ]);
});

Route::prefix('v1')->group(function () {

    // === AUTENTIKASI PUBLIK ===
    Route::post('/auth/login', [AuthController::class, 'login']);

    // === ROUTE TERPROTEKSI TOKEN SANCTUM ===
    Route::middleware('auth:sanctum')->group(function () {

        // Profil & Sesi
        Route::get('/auth/profil', [AuthController::class, 'profil']);
        Route::post('/auth/profil', [AuthController::class, 'updateProfil']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard Adaptif Berdasarkan Peran
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Organisasi & Hierarki
        Route::get('/organisasi/kantor', [OrganisasiController::class, 'kantor']);
        Route::post('/organisasi/kantor', [OrganisasiController::class, 'storeKantor'])->middleware('peran:admin');
        Route::get('/organisasi/divisi', [OrganisasiController::class, 'divisi']);
        Route::post('/organisasi/divisi', [OrganisasiController::class, 'storeDivisi'])->middleware('peran:admin');

        // Pengguna, Peran, & Hak Akses
        Route::get('/pengguna', [PenggunaController::class, 'index']);
        Route::post('/pengguna', [PenggunaController::class, 'store'])->middleware('peran:admin');
        Route::get('/peran', [PenggunaController::class, 'peran']);
        Route::get('/hak-akses', [PenggunaController::class, 'hakAkses']);

        // Master Data (Kategori, Vendor, Kontrak Sewa)
        Route::get('/master/kategori', [MasterController::class, 'kategori']);
        Route::post('/master/kategori', [MasterController::class, 'storeKategori'])->middleware('peran:admin,manajer');
        Route::get('/master/vendor', [MasterController::class, 'vendor']);
        Route::post('/master/vendor', [MasterController::class, 'storeVendor'])->middleware('peran:admin,manajer');
        Route::get('/master/kontrak-sewa', [MasterController::class, 'kontrakSewa']);

        // Aset & Pemindaian Barcode/QR
        Route::get('/aset', [AsetController::class, 'index']);
        Route::get('/aset/scan/{kode}', [AsetController::class, 'scan'])->where('kode', '.*');
        Route::get('/aset/{id}', [AsetController::class, 'show']);
        Route::get('/aset/{id}/barcode', [AsetController::class, 'barcode']);
        Route::post('/aset', [AsetController::class, 'store'])->middleware('peran:admin,manajer');
        Route::put('/aset/{id}', [AsetController::class, 'update']);
        Route::post('/aset/{id}/inventarisasi', [AsetController::class, 'inventarisasi']);

        // Penugasan Staf & Validasi Manajer
        Route::get('/penugasan', [PenugasanController::class, 'index']);
        Route::post('/penugasan', [PenugasanController::class, 'store'])->middleware('peran:admin,manajer');
        Route::post('/penugasan/{id}/kerjakan', [PenugasanController::class, 'kerjakan']);
        Route::post('/penugasan/{id}/kirim-validasi', [PenugasanController::class, 'kirimValidasi']);
        Route::post('/penugasan/{id}/validasi', [PenugasanController::class, 'validasi'])->middleware('peran:admin,manajer');

        // Pengadaan & Persetujuan Kepala Kantor
        Route::get('/pengadaan', [PengadaanController::class, 'index']);
        Route::post('/pengadaan', [PengadaanController::class, 'store'])->middleware('peran:admin,manajer');
        Route::post('/pengadaan/{id}/putusan', [PengadaanController::class, 'putusan'])->middleware('peran:admin,kepala_kantor');

        // Pemeliharaan & Jadwal Servis
        Route::get('/pemeliharaan/jadwal', [PemeliharaanController::class, 'jadwal']);
        Route::get('/pemeliharaan/riwayat', [PemeliharaanController::class, 'riwayat']);
        Route::post('/pemeliharaan', [PemeliharaanController::class, 'store']);

        // Berita Acara Serah Terima (BAST) & Tanda Tangan Elektronik
        Route::get('/bast', [BastController::class, 'index']);
        Route::post('/bast', [BastController::class, 'store']);
        Route::post('/bast/{id}/tanda-tangan', [BastController::class, 'tandaTangan']);
        Route::post('/bast/{id}/sahkan', [BastController::class, 'sahkan'])->middleware('peran:admin,manajer');
        Route::get('/bast/{id}/cetak-pdf', [BastController::class, 'cetakPdf']);

        // Depresiasi & Rekomendasi Peremajaan
        Route::get('/depresiasi', [DepresiasiController::class, 'index']);
        Route::post('/depresiasi/hitung-batch', [DepresiasiController::class, 'hitungBatch'])->middleware('peran:admin,manajer');
        Route::get('/depresiasi/rekomendasi', [DepresiasiController::class, 'rekomendasi']);

        // Notifikasi Pengguna
        Route::get('/notifikasi', [NotifikasiController::class, 'index']);
        Route::put('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca']);
        Route::put('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua']);

        // Laporan Korporasi & Ekspor PDF
        Route::get('/laporan/ringkasan', [LaporanController::class, 'ringkasan']);
        Route::get('/laporan/aset-pdf', [LaporanController::class, 'asetPdf']);
    });
});
