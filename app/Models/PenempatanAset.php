<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenempatanAset extends Model
{
    protected $table = 'penempatan_aset';

    protected $fillable = [
        'aset_id',
        'kantor_id',
        'divisi_id',
        'penanggung_jawab_id',
        'tanggal_penempatan',
        'status_penempatan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penempatan' => 'date',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'penanggung_jawab_id');
    }
}
