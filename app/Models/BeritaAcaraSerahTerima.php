<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeritaAcaraSerahTerima extends Model
{
    protected $table = 'berita_acara_serah_terima';

    protected $fillable = [
        'nomor_bast',
        'tanggal_bast',
        'jenis_transaksi',
        'kantor_id',
        'divisi_id',
        'pihak_penyerah_id',
        'pihak_penerima_id',
        'disetujui_manajer_id',
        'status_bast',
        'catatan',
        'dokumen_pdf',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bast' => 'date',
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

    public function pihakPenyerah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pihak_penyerah_id');
    }

    public function pihakPenerima(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pihak_penerima_id');
    }

    public function disetujuiManajer(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'disetujui_manajer_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailBast::class, 'berita_acara_serah_terima_id');
    }

    public function tandaTangan(): HasMany
    {
        return $this->hasMany(TandaTanganElektronik::class, 'entitas_id')->where('entitas_tipe', 'bast');
    }
}
