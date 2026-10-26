<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengeluaranAset extends Model
{
    protected $table = 'pengeluaran_aset';

    protected $fillable = [
        'nomor_pengeluaran',
        'tanggal_pengeluaran',
        'kantor_asal_id',
        'kantor_tujuan_id',
        'divisi_tujuan_id',
        'pengguna_pengeluar_id',
        'pengguna_penerima_id',
        'alasan_pengeluaran',
        'status_pengeluaran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengeluaran' => 'date',
        ];
    }

    public function kantorAsal(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_asal_id');
    }

    public function kantorTujuan(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_tujuan_id');
    }

    public function penggunaPengeluar(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_pengeluar_id');
    }

    public function penggunaPenerima(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_penerima_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPengeluaranAset::class, 'pengeluaran_aset_id');
    }
}
