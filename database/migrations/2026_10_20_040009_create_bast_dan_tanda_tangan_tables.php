<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acara_serah_terima', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bast', 50)->unique();
            $table->date('tanggal_bast');
            $table->enum('jenis_transaksi', [
                'penyerahan_karyawan', 'pengembalian', 'pemindahan', 
                'penggantian', 'penarikan', 'pengadaan_baru'
            ])->index();
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->foreignId('pihak_penyerah_id')->constrained('pengguna');
            $table->foreignId('pihak_penerima_id')->constrained('pengguna');
            $table->foreignId('disetujui_manajer_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->enum('status_bast', [
                'draft', 'menunggu_tanda_tangan', 'menunggu_persetujuan_manajer', 
                'selesai_disahkan', 'dibatalkan'
            ])->default('draft')->index();
            $table->text('catatan')->nullable();
            $table->string('dokumen_pdf', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('detail_bast', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_acara_serah_terima_id')->constrained('berita_acara_serah_terima')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset');
            $table->string('kondisi_saat_serah', 50);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('tanda_tangan_elektronik', function (Blueprint $table) {
            $table->id();
            $table->string('entitas_tipe', 50)->index();
            $table->unsignedBigInteger('entitas_id')->index();
            $table->foreignId('penanda_tangan_id')->constrained('pengguna');
            $table->string('peran_penanda_tangan', 50);
            $table->string('file_tanda_tangan', 255);
            $table->string('hash_integritas_dokumen', 64);
            $table->dateTime('waktu_tanda_tangan');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_tangan_elektronik');
        Schema::dropIfExists('detail_bast');
        Schema::dropIfExists('berita_acara_serah_terima');
    }
};
