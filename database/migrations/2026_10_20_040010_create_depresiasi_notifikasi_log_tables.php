<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyusutan_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->smallInteger('periode_tahun')->index();
            $table->tinyInteger('periode_bulan')->index();
            $table->decimal('nilai_perolehan', 15, 2);
            $table->decimal('beban_penyusutan', 15, 2);
            $table->decimal('akumulasi_penyusutan', 15, 2);
            $table->decimal('nilai_buku', 15, 2);
            $table->string('metode_penyusutan', 50)->default('garis_lurus');
            $table->boolean('dihitung_otomatis')->default(true);
            $table->date('tanggal_perhitungan');
            $table->timestamps();

            $table->unique(['aset_id', 'periode_tahun', 'periode_bulan'], 'unik_penyusutan_aset_periode');
        });

        Schema::create('rekomendasi_peremajaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->integer('skor_kondisi')->default(0);
            $table->integer('frekuensi_kerusakan')->default(0);
            $table->decimal('total_biaya_pemeliharaan', 15, 2)->default(0);
            $table->decimal('persentase_usia_terpakai', 5, 2)->default(0);
            $table->enum('prioritas_rekomendasi', ['rendah', 'sedang', 'tinggi', 'segera'])->default('sedang');
            $table->text('alasan_rekomendasi');
            $table->decimal('estimasi_biaya_penggantian', 15, 2)->default(0);
            $table->enum('status_evaluasi', ['menunggu', 'disetujui_ganti', 'ditunda', 'dibatalkan'])->default('menunggu');
            $table->timestamps();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->text('pesan');
            $table->string('tipe_notifikasi', 50)->index();
            $table->string('referensi_tipe', 50)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->boolean('sudah_dibaca')->default(false)->index();
            $table->dateTime('waktu_dibaca')->nullable();
            $table->timestamps();
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('kantor_id')->nullable()->constrained('kantor')->nullOnDelete();
            $table->string('modul', 50)->index();
            $table->string('aksi', 50)->index();
            $table->text('deskripsi');
            $table->json('data_sebelumnya')->nullable();
            $table->json('data_sesudahnya')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->id();
            $table->string('kunci_pengaturan', 60)->unique();
            $table->text('nilai_pengaturan');
            $table->string('kategori', 50)->index();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('rekomendasi_peremajaan');
        Schema::dropIfExists('penyusutan_aset');
    }
};
