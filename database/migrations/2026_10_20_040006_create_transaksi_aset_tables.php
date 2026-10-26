<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('jenis_aktivitas', 50)->index();
            $table->foreignId('kantor_asal_id')->nullable()->constrained('kantor')->nullOnDelete();
            $table->foreignId('kantor_tujuan_id')->nullable()->constrained('kantor')->nullOnDelete();
            $table->foreignId('divisi_asal_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->foreignId('divisi_tujuan_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->string('kondisi_sebelumnya', 50)->nullable();
            $table->string('kondisi_sesudahnya', 50)->nullable();
            $table->string('status_sebelumnya', 50)->nullable();
            $table->string('status_sesudahnya', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->json('nilai_lama')->nullable();
            $table->json('nilai_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('penerimaan_aset', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penerimaan', 50)->unique();
            $table->date('tanggal_penerimaan');
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->foreignId('vendor_id')->nullable()->constrained('vendor')->nullOnDelete();
            $table->string('nomor_po_pengadaan', 50)->nullable();
            $table->foreignId('pengguna_penerima_id')->constrained('pengguna');
            $table->string('kondisi_penerimaan', 50)->default('baik');
            $table->enum('status_penerimaan', ['draft', 'terverifikasi', 'dibatalkan'])->default('terverifikasi');
            $table->text('catatan')->nullable();
            $table->string('dokumen_surat_jalan', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('detail_penerimaan_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_aset_id')->constrained('penerimaan_aset')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset');
            $table->string('kondisi', 50)->default('baik');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengeluaran_aset', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengeluaran', 50)->unique();
            $table->date('tanggal_pengeluaran');
            $table->foreignId('kantor_asal_id')->constrained('kantor');
            $table->foreignId('kantor_tujuan_id')->nullable()->constrained('kantor')->nullOnDelete();
            $table->foreignId('divisi_tujuan_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->foreignId('pengguna_pengeluar_id')->constrained('pengguna');
            $table->foreignId('pengguna_penerima_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('alasan_pengeluaran', 100);
            $table->enum('status_pengeluaran', ['draft', 'disetujui', 'keluar', 'selesai'])->default('selesai');
            $table->timestamps();
        });

        Schema::create('detail_pengeluaran_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengeluaran_aset_id')->constrained('pengeluaran_aset')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset');
            $table->string('kondisi', 50)->default('baik');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('penempatan_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->date('tanggal_penempatan');
            $table->enum('status_penempatan', ['aktif', 'selesai'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penempatan_aset');
        Schema::dropIfExists('detail_pengeluaran_aset');
        Schema::dropIfExists('pengeluaran_aset');
        Schema::dropIfExists('detail_penerimaan_aset');
        Schema::dropIfExists('penerimaan_aset');
        Schema::dropIfExists('riwayat_aset');
    }
};
