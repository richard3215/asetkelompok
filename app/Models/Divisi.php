<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    protected $table = 'divisi';

    protected $fillable = [
        'kode_divisi',
        'nama_divisi',
        'kantor_id',
        'induk_divisi_id',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'induk_divisi_id');
    }

    public function subDivisi(): HasMany
    {
        return $this->hasMany(Divisi::class, 'induk_divisi_id');
    }

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'divisi_id');
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'divisi_id');
    }
}
