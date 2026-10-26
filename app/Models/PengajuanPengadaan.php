<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanPengadaan extends Model
{
    protected $table = 'pengajuan_pengadaan';

    protected $fillable = [
        'nomor_pengadaan',
        'kantor_id',
        'divisi_id',
        'pengaju_id',
        'judul_pengadaan',
        'deskripsi_kebutuhan',
        'alasan_pengadaan',
        'estimasi_total_biaya',
        'status_pengadaan',
        'catatan_penolakan',
        'tanggal_pengajuan',
        'tanggal_disetujui',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_disetujui' => 'date',
            'estimasi_total_biaya' => 'decimal:2',
        ];
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengaju_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPengajuanPengadaan::class, 'pengajuan_pengadaan_id');
    }

    public function persetujuan(): HasMany
    {
        return $this->hasMany(Persetujuan::class, 'dokumen_id')->where('tipe_dokumen', 'pengadaan');
    }
}
