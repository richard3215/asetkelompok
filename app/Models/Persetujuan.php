<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persetujuan extends Model
{
    protected $table = 'persetujuan';

    protected $fillable = [
        'nomor_dokumen_referensi',
        'tipe_dokumen',
        'dokumen_id',
        'pemohon_id',
        'penyetuju_id',
        'level_persetujuan',
        'status_keputusan',
        'komentar',
        'tanggal_keputusan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keputusan' => 'datetime',
            'level_persetujuan' => 'integer',
        ];
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pemohon_id');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'penyetuju_id');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPersetujuan::class, 'persetujuan_id')->latest();
    }
}
