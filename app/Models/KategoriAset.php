<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriAset extends Model
{
    protected $table = 'kategori_aset';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'tipe_aset',
        'masa_manfaat_bulan',
        'persentase_residu',
        'metode_penyusutan_default',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'masa_manfaat_bulan' => 'integer',
            'persentase_residu' => 'decimal:2',
        ];
    }

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'kategori_aset_id');
    }
}
