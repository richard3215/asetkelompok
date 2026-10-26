<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBast extends Model
{
    protected $table = 'detail_bast';

    protected $fillable = [
        'berita_acara_serah_terima_id',
        'aset_id',
        'kondisi_saat_serah',
        'keterangan',
    ];

    public function bast(): BelongsTo
    {
        return $this->belongsTo(BeritaAcaraSerahTerima::class, 'berita_acara_serah_terima_id');
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
