<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengadaan', 50)->unique();
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->foreignId('pengaju_id')->constrained('pengguna');
            $table->string('judul_pengadaan', 200);
            $table->text('deskripsi_kebutuhan');
            $table->text('alasan_pengadaan');
            $table->decimal('estimasi_total_biaya', 15, 2)->default(0);
            $table->enum('status_pengadaan', [
                'draft', 'diajukan', 'menunggu_persetujuan_kepala_kantor', 
                'disetujui', 'ditolak', 'perlu_revisi', 'selesai'
            ])->default('draft')->index();
            $table->text('catatan_penolakan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_disetujui')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_pengajuan_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_pengadaan_id')->constrained('pengajuan_pengadaan')->cascadeOnDelete();
            $table->foreignId('kategori_aset_id')->constrained('kategori_aset');
            $table->string('nama_barang', 150);
            $table->text('spesifikasi')->nullable();
            $table->integer('jumlah')->default(1);
            $table->decimal('estimasi_harga_satuan', 15, 2)->default(0);
            $table->decimal('estimasi_subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('persetujuan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_dokumen_referensi', 50)->index();
            $table->enum('tipe_dokumen', ['pengadaan', 'bast', 'pemeliharaan', 'mutasi', 'penghapusan'])->index();
            $table->unsignedBigInteger('dokumen_id')->index();
            $table->foreignId('pemohon_id')->constrained('pengguna');
            $table->foreignId('penyetuju_id')->constrained('pengguna');
            $table->tinyInteger('level_persetujuan')->default(1);
            $table->enum('status_keputusan', ['menunggu', 'disetujui', 'ditolak', 'dikembalikan'])->default('menunggu')->index();
            $table->text('komentar')->nullable();
            $table->dateTime('tanggal_keputusan')->nullable();
            $table->timestamps();
        });

        Schema::create('riwayat_persetujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persetujuan_id')->constrained('persetujuan')->cascadeOnDelete();
            $table->foreignId('penyetuju_id')->constrained('pengguna');
            $table->enum('aksi', ['diajukan', 'disetujui', 'ditolak', 'dikembalikan']);
            $table->text('catatan')->nullable();
            $table->dateTime('waktu_aksi');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_persetujuan');
        Schema::dropIfExists('persetujuan');
        Schema::dropIfExists('detail_pengajuan_pengadaan');
        Schema::dropIfExists('pengajuan_pengadaan');
    }
};
