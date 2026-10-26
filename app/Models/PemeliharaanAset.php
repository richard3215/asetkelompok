<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeliharaanAset extends Model
{
    protected $table = 'pemeliharaan_aset';

    protected $fillable = [
        'nomor_pemeliharaan',
        'jadwal_pemeliharaan_id',
        'aset_id',
        'pengguna_teknisi_id',
        'vendor_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'biaya_realisasi',
        'deskripsi_kerusakan',
        'tindakan_perbaikan',
        'status',
        'foto_sebelum',
        'foto_sesudah',
        'dokumen_faktur',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'biaya_realisasi' => 'decimal:2',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPemeliharaan::class, 'jadwal_pemeliharaan_id');
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_teknisi_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
