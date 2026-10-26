<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranKegiatan extends Model
{
    protected $table = 'lampiran_kegiatan';

    protected $fillable = [
        'entitas_tipe',
        'entitas_id',
        'jenis_lampiran',
        'file_path',
        'nama_file_asli',
        'mime_type',
        'ukuran_file',
        'diunggah_oleh_id',
    ];

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diunggah_oleh_id');
    }
}
