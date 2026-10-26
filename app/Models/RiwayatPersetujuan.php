<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPersetujuan extends Model
{
    protected $table = 'riwayat_persetujuan';

    protected $fillable = [
        'persetujuan_id',
        'penyetuju_id',
        'aksi',
        'catatan',
        'waktu_aksi',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'waktu_aksi' => 'datetime',
        ];
    }

    public function persetujuan(): BelongsTo
    {
        return $this->belongsTo(Persetujuan::class, 'persetujuan_id');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'penyetuju_id');
    }
}
