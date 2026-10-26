<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyusutanAset extends Model
{
    protected $table = 'penyusutan_aset';

    protected $fillable = [
        'aset_id',
        'periode_tahun',
        'periode_bulan',
        'nilai_perolehan',
        'beban_penyusutan',
        'akumulasi_penyusutan',
        'nilai_buku',
        'metode_penyusutan',
        'dihitung_otomatis',
        'tanggal_perhitungan',
    ];

    protected function casts(): array
    {
        return [
            'periode_tahun' => 'integer',
            'periode_bulan' => 'integer',
            'nilai_perolehan' => 'decimal:2',
            'beban_penyusutan' => 'decimal:2',
            'akumulasi_penyusutan' => 'decimal:2',
            'nilai_buku' => 'decimal:2',
            'dihitung_otomatis' => 'boolean',
            'tanggal_perhitungan' => 'date',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
