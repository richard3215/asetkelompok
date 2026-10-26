<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->unique();
            $table->string('nama', 150);
            $table->string('email', 150)->unique();
            $table->string('kata_sandi', 255);
            $table->string('nomor_telepon', 30)->nullable();
            $table->foreignId('peran_id')->constrained('peran');
            $table->foreignId('kantor_id')->constrained('kantor');
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->nullOnDelete();
            $table->string('foto_profil', 255)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamp('terakhir_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
