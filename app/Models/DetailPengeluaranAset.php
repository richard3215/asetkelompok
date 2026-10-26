<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengeluaranAset extends Model
{
    protected $table = 'detail_pengeluaran_aset';

    protected $fillable = [
        'pengeluaran_aset_id',
        'aset_id',
        'kondisi',
        'catatan',
    ];

    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(PengeluaranAset::class, 'pengeluaran_aset_id');
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
