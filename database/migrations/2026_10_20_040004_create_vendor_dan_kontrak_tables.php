<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor', function (Blueprint $table) {
            $table->id();
            $table->string('kode_vendor', 30)->unique();
            $table->string('nama_vendor', 150);
            $table->string('kontak_person', 100)->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('kontrak_sewa', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kontrak', 50)->unique();
            $table->foreignId('vendor_id')->constrained('vendor');
            $table->string('judul_kontrak', 200);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('biaya_sewa', 15, 2)->default(0);
            $table->enum('periode_pembayaran', ['bulanan', 'kuartal', 'semester', 'tahunan'])->default('bulanan');
            $table->enum('status_kontrak', ['aktif', 'berakhir', 'diperpanjang', 'dibatalkan'])->default('aktif');
            $table->text('ketentuan_pemeliharaan')->nullable();
            $table->enum('penanggung_jawab_pemeliharaan', ['vendor', 'internal'])->default('vendor');
            $table->string('dokumen_kontrak', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak_sewa');
        Schema::dropIfExists('vendor');
    }
};
