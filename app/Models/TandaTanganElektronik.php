<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TandaTanganElektronik extends Model
{
    protected $table = 'tanda_tangan_elektronik';

    protected $fillable = [
        'entitas_tipe',
        'entitas_id',
        'penanda_tangan_id',
        'peran_penanda_tangan',
        'file_tanda_tangan',
        'hash_integritas_dokumen',
        'waktu_tanda_tangan',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'waktu_tanda_tangan' => 'datetime',
        ];
    }

    public function penandaTangan(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'penanda_tangan_id');
    }
}
