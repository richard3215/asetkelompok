<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_peran', 30)->unique();
            $table->string('nama_peran', 100);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('hak_akses', function (Blueprint $table) {
            $table->id();
            $table->string('kode_hak_akses', 60)->unique();
            $table->string('nama_hak_akses', 100);
            $table->string('modul', 50)->index();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('peran_hak_akses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peran_id')->constrained('peran')->cascadeOnDelete();
            $table->foreignId('hak_akses_id')->constrained('hak_akses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['peran_id', 'hak_akses_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peran_hak_akses');
        Schema::dropIfExists('hak_akses');
        Schema::dropIfExists('peran');
    }
};
