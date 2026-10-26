<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_pemeliharaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->enum('jenis_pemeliharaan', ['rutin', 'preventif', 'korektif', 'darurat'])->default('rutin');
            $table->integer('interval_hari')->default(90);
            $table->date('tanggal_terakhir')->nullable();
            $table->date('tanggal_berikutnya')->index();
            $table->decimal('biaya_estimasi', 15, 2)->default(0);
            $table->enum('status', ['terjadwal', 'mendekati_tenggat', 'terlambat', 'selesai', 'ditunda'])->default('terjadwal');
            $table->timestamps();
        });

        Schema::create('pemeliharaan_aset', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pemeliharaan', 50)->unique();
            $table->foreignId('jadwal_pemeliharaan_id')->nullable()->constrained('jadwal_pemeliharaan')->nullOnDelete();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('pengguna_teknisi_id')->constrained('pengguna');
            $table->foreignId('vendor_id')->nullable()->constrained('vendor')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('biaya_realisasi', 15, 2)->default(0);
            $table->text('deskripsi_kerusakan');
            $table->text('tindakan_perbaikan')->nullable();
            $table->enum('status', ['dalam_pengerjaan', 'menunggu_validasi', 'disetujui', 'ditolak'])->default('dalam_pengerjaan');
            $table->string('foto_sebelum', 255)->nullable();
            $table->string('foto_sesudah', 255)->nullable();
            $table->string('dokumen_faktur', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('penugasan_staf', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penugasan', 50)->unique();
            $table->foreignId('pemberi_tugas_id')->constrained('pengguna');
            $table->foreignId('staf_pelaksana_id')->constrained('pengguna');
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->enum('jenis_penugasan', [
                'inventarisasi', 'pemeriksaan_kondisi', 'pemeliharaan', 
                'penggantian', 'penarikan', 'pengembalian', 'pemindahan', 'pelabelan_qr'
            ]);
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'darurat'])->default('sedang');
            $table->date('tanggal_penugasan');
            $table->date('tenggat_waktu');
            $table->text('instruksi');
            $table->enum('status_penugasan', [
                'diberikan', 'sedang_dikerjakan', 'menunggu_validasi', 'revisi', 'selesai', 'dibatalkan'
            ])->default('diberikan');
            $table->text('catatan_pelaksanaan')->nullable();
            $table->text('alasan_revisi')->nullable();
            $table->dateTime('tanggal_selesai_pelaksanaan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_penugasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penugasan_staf_id')->constrained('penugasan_staf')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset');
            $table->string('kondisi_sebelum', 50)->nullable();
            $table->string('kondisi_sesudah', 50)->nullable();
            $table->string('lokasi_ditemukan', 150)->nullable();
            $table->enum('status_pengerjaan', ['belum', 'selesai', 'kendala'])->default('belum');
            $table->text('catatan_staf')->nullable();
            $table->timestamps();
        });

        Schema::create('lampiran_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('entitas_tipe', 50)->index();
            $table->unsignedBigInteger('entitas_id')->index();
            $table->string('jenis_lampiran', 50);
            $table->string('file_path', 255);
            $table->string('nama_file_asli', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('ukuran_file')->default(0);
            $table->foreignId('diunggah_oleh_id')->constrained('pengguna');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_kegiatan');
        Schema::dropIfExists('detail_penugasan');
        Schema::dropIfExists('penugasan_staf');
        Schema::dropIfExists('pemeliharaan_aset');
        Schema::dropIfExists('jadwal_pemeliharaan');
    }
};
