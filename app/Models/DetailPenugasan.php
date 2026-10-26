<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenugasan extends Model
{
    protected $table = 'detail_penugasan';

    protected $fillable = [
        'penugasan_staf_id',
        'aset_id',
        'kondisi_sebelum',
        'kondisi_sesudah',
        'lokasi_ditemukan',
        'status_pengerjaan',
        'catatan_staf',
    ];

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(PenugasanStaf::class, 'penugasan_staf_id');
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
