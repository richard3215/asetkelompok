<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peran extends Model
{
    protected $table = 'peran';

    protected $fillable = [
        'kode_peran',
        'nama_peran',
        'deskripsi',
    ];

    public function hakAkses(): BelongsToMany
    {
        return $this->belongsToMany(HakAkses::class, 'peran_hak_akses', 'peran_id', 'hak_akses_id')->withTimestamps();
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'peran_id');
    }
}
