<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_aset', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori', 30)->unique();
            $table->string('nama_kategori', 100);
            $table->enum('tipe_aset', ['perusahaan', 'sewa'])->default('perusahaan');
            $table->integer('masa_manfaat_bulan')->default(48);
            $table->decimal('persentase_residu', 5, 2)->default(0.00);
            $table->enum('metode_penyusutan_default', ['garis_lurus', 'saldo_menurun', 'tanpa_penyusutan'])->default('garis_lurus');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('penomoran_counter', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20);
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->unsignedInteger('nomor_terakhir')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'tahun', 'bulan']);
        });

        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_index', 50)->unique();
            $table->string('nomor_seri', 100)->nullable()->index();
            $table->string('barcode_vendor', 100)->nullable()->index();
            $table->string('nama_aset', 200)->index();
            $table->foreignId('kategori_aset_id')->constrained('kategori_aset');
            $table->enum('jenis_kepemilikan', ['milik_sendiri', 'sewa_vendor'])->default('milik_sendiri')->index();
            $table->foreignId('kontrak_sewa_id')->nullable()->constrained('kontrak_sewa')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendor')->nullOnDelete();
            $table->string('merk', 100)->nullable();
            $table->string('tipe_model', 100)->nullable();
            $table->enum('kondisi', ['sangat_baik', 'baik', 'rusak_ringan', 'rusak_berat', 'tidak_ekonomis'])->default('baik')->index();
            $table->enum('status_operasional', [
                'draft', 'diterima', 'terdaftar', 'aktif', 'dalam_pemeliharaan', 
                'menunggu_persetujuan', 'diganti', 'dipindahkan', 'dikembalikan', 'dihapuskan'
            ])->default('terdaftar')->index();
            $table->foreignId('kantor_id')->constrained('kantor')->index();
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->date('tanggal_perolehan');
            $table->string('sumber_pengadaan', 150)->nullable();
            $table->decimal('nilai_perolehan', 15, 2)->default(0);
            $table->decimal('nilai_residu', 15, 2)->default(0);
            $table->integer('masa_manfaat_bulan')->default(48);
            $table->enum('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'tanpa_penyusutan'])->default('garis_lurus');
            $table->date('tanggal_mulai_penyusutan')->nullable();
            $table->decimal('nilai_buku_saat_ini', 15, 2)->default(0);
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            $table->string('foto_aset', 255)->nullable();
            $table->string('dokumen_pendukung', 255)->nullable();
            $table->text('spesifikasi_teknis')->nullable();
            $table->enum('status_inventaris', ['ditemukan', 'lokasi_berbeda', 'rusak', 'hilang', 'belum_diperiksa'])->default('belum_diperiksa');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset');
        Schema::dropIfExists('penomoran_counter');
        Schema::dropIfExists('kategori_aset');
    }
};
