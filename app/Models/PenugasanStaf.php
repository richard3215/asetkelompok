<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenugasanStaf extends Model
{
    protected $table = 'penugasan_staf';

    protected $fillable = [
        'nomor_penugasan',
        'pemberi_tugas_id',
        'staf_pelaksana_id',
        'kantor_id',
        'jenis_penugasan',
        'prioritas',
        'tanggal_penugasan',
        'tenggat_waktu',
        'instruksi',
        'status_penugasan',
        'catatan_pelaksanaan',
        'alasan_revisi',
        'tanggal_selesai_pelaksanaan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penugasan' => 'date',
            'tenggat_waktu' => 'date',
            'tanggal_selesai_pelaksanaan' => 'datetime',
        ];
    }

    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pemberi_tugas_id');
    }

    public function stafPelaksana(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'staf_pelaksana_id');
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenugasan::class, 'penugasan_staf_id');
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(LampiranKegiatan::class, 'entitas_id')->where('entitas_tipe', 'penugasan');
    }
}
