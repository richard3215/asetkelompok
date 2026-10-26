<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kantor extends Model
{
    protected $table = 'kantor';

    protected $fillable = [
        'kode_kantor',
        'nama_kantor',
        'jenis_kantor',
        'induk_kantor_id',
        'alamat',
        'nomor_telepon',
        'email',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'induk_kantor_id');
    }

    public function cabang(): HasMany
    {
        return $this->hasMany(Kantor::class, 'induk_kantor_id');
    }

    public function divisi(): HasMany
    {
        return $this->hasMany(Divisi::class, 'kantor_id');
    }

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'kantor_id');
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'kantor_id');
    }
}
