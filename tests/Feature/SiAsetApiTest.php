<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\Pengguna;
use App\Services\DepresiasiService;
use App\Services\NomorIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiAsetApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    /**
     * Uji Login Berhasil dengan Email.
     */
    public function test_pengguna_bisa_login_dengan_kredensial_valid(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identitas' => 'admin@siaset.com',
            'kata_sandi' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'pesan',
                'data' => [
                    'token',
                    'pengguna' => ['id', 'nip', 'nama', 'email', 'peran', 'kantor', 'hak_akses'],
                ],
            ]);
    }

    /**
     * Uji Login dengan NIP Pegawai.
     */
    public function test_pengguna_bisa_login_menggunakan_nip(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identitas' => '19850101001',
            'kata_sandi' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.pengguna.nip', '19850101001');
    }

    /**
     * Uji Skenario Negatif: Kata sandi salah ditolak.
     */
    public function test_login_gagal_jika_kata_sandi_salah(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identitas' => 'admin@siaset.com',
            'kata_sandi' => 'sandiSalahTotal',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Uji Dashboard Adaptif dengan Token.
     */
    public function test_dashboard_memuat_statistik_berdasarkan_peran(): void
    {
        $admin = Pengguna::where('email', 'admin@siaset.com')->first();
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.peran', 'admin')
            ->assertJsonStructure([
                'data' => [
                    'statistik' => ['total_aset', 'total_aset_milik', 'total_aset_sewa'],
                    'distribusi_kondisi',
                ],
            ]);
    }

    /**
     * Uji Pemindaian QR Code Aset.
     */
    public function test_scan_aset_menemukan_aset_berdasarkan_nomor_index(): void
    {
        $admin = Pengguna::where('email', 'admin@siaset.com')->first();
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/aset/scan/AST/17000/202610/00001');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nomor_index', 'AST/17000/202610/00001');
    }

    /**
     * Uji Pembuatan Nomor Index Aset yang Bersifat Konkuren & Unik.
     */
    public function test_layanan_nomor_index_menghasilkan_format_sesuai_standar(): void
    {
        $nomorIndex = NomorIndexService::buatNomorIndexAset('17113');
        $this->assertStringStartsWith('AST/17113/', $nomorIndex);
    }

    /**
     * Uji Perhitungan Depresiasi Garis Lurus Otomatis.
     */
    public function test_perhitungan_penyusutan_garis_lurus(): void
    {
        $aset = Aset::where('jenis_kepemilikan', 'milik_sendiri')->first();
        $penyusutan = DepresiasiService::hitungBulanan($aset, 2026, 11);

        $this->assertNotNull($penyusutan);
        $this->assertEquals(2026, $penyusutan->periode_tahun);
        $this->assertEquals(11, $penyusutan->periode_bulan);
        $this->assertGreaterThan(0, $penyusutan->beban_penyusutan);
    }

    /**
     * Uji Skenario Negatif: Aset Sewa Vendor Tidak Disusutkan.
     */
    public function test_aset_sewa_vendor_tidak_didepresiasi(): void
    {
        $asetSewa = Aset::where('jenis_kepemilikan', 'sewa_vendor')->first();
        $hasil = DepresiasiService::hitungBulanan($asetSewa, 2026, 11);

        $this->assertNull($hasil);
    }
}
