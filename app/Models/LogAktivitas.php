<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'pengguna_id',
        'kantor_id',
        'modul',
        'aksi',
        'deskripsi',
        'data_sebelumnya',
        'data_sesudahnya',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'data_sebelumnya' => 'array',
            'data_sesudahnya' => 'array',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }
}
