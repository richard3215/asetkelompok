<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPemeliharaan extends Model
{
    protected $table = 'jadwal_pemeliharaan';

    protected $fillable = [
        'aset_id',
        'kantor_id',
        'jenis_pemeliharaan',
        'interval_hari',
        'tanggal_terakhir',
        'tanggal_berikutnya',
        'biaya_estimasi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terakhir' => 'date',
            'tanggal_berikutnya' => 'date',
            'biaya_estimasi' => 'decimal:2',
            'interval_hari' => 'integer',
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

    public function riwayatPemeliharaan(): HasMany
    {
        return $this->hasMany(PemeliharaanAset::class, 'jadwal_pemeliharaan_id');
    }
}
