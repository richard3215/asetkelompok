<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanAset extends Model
{
    protected $table = 'penerimaan_aset';

    protected $fillable = [
        'nomor_penerimaan',
        'tanggal_penerimaan',
        'kantor_id',
        'vendor_id',
        'nomor_po_pengadaan',
        'pengguna_penerima_id',
        'kondisi_penerimaan',
        'status_penerimaan',
        'catatan',
        'dokumen_surat_jalan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penerimaan' => 'date',
        ];
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function penggunaPenerima(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_penerima_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenerimaanAset::class, 'penerimaan_aset_id');
    }
}
