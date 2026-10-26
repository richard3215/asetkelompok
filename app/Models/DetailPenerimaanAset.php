<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenerimaanAset extends Model
{
    protected $table = 'detail_penerimaan_aset';

    protected $fillable = [
        'penerimaan_aset_id',
        'aset_id',
        'kondisi',
        'catatan',
    ];

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanAset::class, 'penerimaan_aset_id');
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
