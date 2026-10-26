<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KontrakSewa extends Model
{
    protected $table = 'kontrak_sewa';

    protected $fillable = [
        'nomor_kontrak',
        'vendor_id',
        'judul_kontrak',
        'tanggal_mulai',
        'tanggal_selesai',
        'biaya_sewa',
        'periode_pembayaran',
        'status_kontrak',
        'ketentuan_pemeliharaan',
        'penanggung_jawab_pemeliharaan',
        'dokumen_kontrak',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'biaya_sewa' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'kontrak_sewa_id');
    }
}
