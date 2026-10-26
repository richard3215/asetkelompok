<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HakAkses extends Model
{
    protected $table = 'hak_akses';

    protected $fillable = [
        'kode_hak_akses',
        'nama_hak_akses',
        'modul',
        'deskripsi',
    ];

    public function peran(): BelongsToMany
    {
        return $this->belongsToMany(Peran::class, 'peran_hak_akses', 'hak_akses_id', 'peran_id')->withTimestamps();
    }
}
