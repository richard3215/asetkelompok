<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'pengguna';

    protected $fillable = [
        'nip',
        'nama',
        'email',
        'kata_sandi',
        'nomor_telepon',
        'peran_id',
        'kantor_id',
        'divisi_id',
        'foto_profil',
        'status_aktif',
        'terakhir_login',
    ];

    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'terakhir_login' => 'datetime',
        ];
    }

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function peran(): BelongsTo
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function memilikiPeran(string $kodePeran): bool
    {
        return $this->peran && $this->peran->kode_peran === $kodePeran;
    }

    public function memilikiHakAkses(string $kodeHakAkses): bool
    {
        if ($this->memilikiPeran('admin')) {
            return true;
        }

        return $this->peran && $this->peran->hakAkses()->where('kode_hak_akses', $kodeHakAkses)->exists();
    }
}
