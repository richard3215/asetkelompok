<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenomoranCounter extends Model
{
    protected $table = 'penomoran_counter';

    protected $fillable = [
        'prefix',
        'tahun',
        'bulan',
        'nomor_terakhir',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'nomor_terakhir' => 'integer',
        ];
    }
}
