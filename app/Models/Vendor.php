<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $table = 'vendor';

    protected $fillable = [
        'kode_vendor',
        'nama_vendor',
        'kontak_person',
        'nomor_telepon',
        'email',
        'alamat',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function kontrakSewa(): HasMany
    {
        return $this->hasMany(KontrakSewa::class, 'vendor_id');
    }

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'vendor_id');
    }
}
