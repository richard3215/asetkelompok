<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aset extends Model
{
    use SoftDeletes;

    protected $table = 'aset';

    protected $fillable = [
        'nomor_index',
        'nomor_seri',
        'barcode_vendor',
        'nama_aset',
        'kategori_aset_id',
        'jenis_kepemilikan',
        'kontrak_sewa_id',
        'vendor_id',
        'merk',
        'tipe_model',
        'kondisi',
        'status_operasional',
        'kantor_id',
        'divisi_id',
        'penanggung_jawab_id',
        'tanggal_perolehan',
        'sumber_pengadaan',
        'nilai_perolehan',
        'nilai_residu',
        'masa_manfaat_bulan',
        'metode_penyusutan',
        'tanggal_mulai_penyusutan',
        'nilai_buku_saat_ini',
        'akumulasi_penyusutan',
        'foto_aset',
        'dokumen_pendukung',
        'spesifikasi_teknis',
        'status_inventaris',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perolehan' => 'date',
            'tanggal_mulai_penyusutan' => 'date',
            'nilai_perolehan' => 'decimal:2',
            'nilai_residu' => 'decimal:2',
            'nilai_buku_saat_ini' => 'decimal:2',
            'akumulasi_penyusutan' => 'decimal:2',
            'masa_manfaat_bulan' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_aset_id');
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'penanggung_jawab_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function kontrakSewa(): BelongsTo
    {
        return $this->belongsTo(KontrakSewa::class, 'kontrak_sewa_id');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatAset::class, 'aset_id')->latest();
    }

    public function jadwalPemeliharaan(): HasMany
    {
        return $this->hasMany(JadwalPemeliharaan::class, 'aset_id');
    }

    public function pemeliharaan(): HasMany
    {
        return $this->hasMany(PemeliharaanAset::class, 'aset_id');
    }

    public function penyusutan(): HasMany
    {
        return $this->hasMany(PenyusutanAset::class, 'aset_id');
    }

    public function rekomendasiPeremajaan(): HasMany
    {
        return $this->hasMany(RekomendasiPeremajaan::class, 'aset_id');
    }
}
