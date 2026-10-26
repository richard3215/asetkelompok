<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanPengadaan extends Model
{
    protected $table = 'detail_pengajuan_pengadaan';

    protected $fillable = [
        'pengajuan_pengadaan_id',
        'kategori_aset_id',
        'nama_barang',
        'spesifikasi',
        'jumlah',
        'estimasi_harga_satuan',
        'estimasi_subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'estimasi_harga_satuan' => 'decimal:2',
            'estimasi_subtotal' => 'decimal:2',
        ];
    }

    public function pengadaan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPengadaan::class, 'pengajuan_pengadaan_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_aset_id');
    }
}
