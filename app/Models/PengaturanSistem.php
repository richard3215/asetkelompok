<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'kunci_pengaturan',
        'nilai_pengaturan',
        'kategori',
        'deskripsi',
    ];

    public static function ambil(string $kunci, $default = null)
    {
        $setting = static::where('kunci_pengaturan', $kunci)->first();
        return $setting ? $setting->nilai_pengaturan : $default;
    }
}
