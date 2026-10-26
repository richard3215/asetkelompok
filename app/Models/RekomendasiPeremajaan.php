<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiPeremajaan extends Model
{
    protected $table = 'rekomendasi_peremajaan';

    protected $fillable = [
        'aset_id',
        'skor_kondisi',
        'frekuensi_kerusakan',
        'total_biaya_pemeliharaan',
        'persentase_usia_terpakai',
        'prioritas_rekomendasi',
        'alasan_rekomendasi',
        'estimasi_biaya_penggantian',
        'status_evaluasi',
    ];

    protected function casts(): array
    {
        return [
            'skor_kondisi' => 'integer',
            'frekuensi_kerusakan' => 'integer',
            'total_biaya_pemeliharaan' => 'decimal:2',
            'persentase_usia_terpakai' => 'decimal:2',
            'estimasi_biaya_penggantian' => 'decimal:2',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
