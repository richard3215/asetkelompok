<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kantor', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kantor', 30)->unique();
            $table->string('nama_kantor', 150);
            $table->enum('jenis_kantor', ['pusat', 'cabang', 'unit_pembantu'])->default('cabang');
            $table->foreignId('induk_kantor_id')->nullable()->constrained('kantor')->nullOnDelete();
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('divisi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_divisi', 30)->unique();
            $table->string('nama_divisi', 150);
            $table->foreignId('kantor_id')->constrained('kantor')->cascadeOnDelete();
            $table->foreignId('induk_divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisi');
        Schema::dropIfExists('kantor');
    }
};
