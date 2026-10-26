<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatAset extends Model
{
    protected $table = 'riwayat_aset';

    protected $fillable = [
        'aset_id',
        'pengguna_id',
        'jenis_aktivitas',
        'kantor_asal_id',
        'kantor_tujuan_id',
        'divisi_asal_id',
        'divisi_tujuan_id',
        'kondisi_sebelumnya',
        'kondisi_sesudahnya',
        'status_sebelumnya',
        'status_sesudahnya',
        'keterangan',
        'nilai_lama',
        'nilai_baru',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'nilai_lama' => 'array',
            'nilai_baru' => 'array',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function kantorAsal(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_asal_id');
    }

    public function kantorTujuan(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_tujuan_id');
    }
}
