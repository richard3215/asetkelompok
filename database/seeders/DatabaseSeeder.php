<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\BeritaAcaraSerahTerima;
use App\Models\DetailBast;
use App\Models\DetailPengajuanPengadaan;
use App\Models\DetailPenugasan;
use App\Models\Divisi;
use App\Models\HakAkses;
use App\Models\JadwalPemeliharaan;
use App\Models\Kantor;
use App\Models\KategoriAset;
use App\Models\KontrakSewa;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\PemeliharaanAset;
use App\Models\PengajuanPengadaan;
use App\Models\PengaturanSistem;
use App\Models\Pengguna;
use App\Models\PenomoranCounter;
use App\Models\PenugasanStaf;
use App\Models\Peran;
use App\Models\Persetujuan;
use App\Models\PenyusutanAset;
use App\Models\RekomendasiPeremajaan;
use App\Models\RiwayatAset;
use App\Models\RiwayatPersetujuan;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEED PERAN (10 records)
        $daftarPeran = [
            ['kode_peran' => 'admin', 'nama_peran' => 'Administrator Sistem', 'deskripsi' => 'Pengelola sistem, konfigurasi penuh, dan seluruh master data.'],
            ['kode_peran' => 'kepala_kantor', 'nama_peran' => 'Kepala Kantor', 'deskripsi' => 'Persetujuan akhir pengadaan, biaya setempat, dan pengawasan operasional.'],
            ['kode_peran' => 'manajer', 'nama_peran' => 'Manajer Operasional & Aset', 'deskripsi' => 'Pengelolaan mutasi, penugasan staf, validasi hasil kerja, dan BAST.'],
            ['kode_peran' => 'staf', 'nama_peran' => 'Staf Operasional & Teknisi', 'deskripsi' => 'Pelaksana inventarisasi, pemeliharaan fisik, scan barcode/QR, upload bukti foto.'],
            ['kode_peran' => 'kepala_divisi', 'nama_peran' => 'Kepala Divisi', 'deskripsi' => 'Penanggung jawab alokasi dan inventaris aset tingkat divisi.'],
            ['kode_peran' => 'auditor_internal', 'nama_peran' => 'Auditor Internal', 'deskripsi' => 'Akses pemeriksaan audit trail, pelacakan histori, dan laporan kepatuhan.'],
            ['kode_peran' => 'staf_pengadaan', 'nama_peran' => 'Staf Pengadaan', 'deskripsi' => 'Penyusun permohonan pengadaan barang dan verifikasi vendor.'],
            ['kode_peran' => 'staf_gudang', 'nama_peran' => 'Staf Pergudangan', 'deskripsi' => 'Penerimaan barang fisik, penempatan gudang, dan logistik transit.'],
            ['kode_peran' => 'operator_inventaris', 'nama_peran' => 'Operator Inventarisasi', 'deskripsi' => 'Pelabelan barcode, pencocokan opname aset berkala.'],
            ['kode_peran' => 'teknisi_pemeliharaan', 'nama_peran' => 'Teknisi Pemeliharaan Khusus', 'deskripsi' => 'Servis hardware, perawatan preventif berkala, dan penanganan darurat.'],
        ];
        $peranMap = [];
        foreach ($daftarPeran as $p) {
            $peranMap[$p['kode_peran']] = Peran::create($p);
        }

        // 2. SEED HAK AKSES (15 records)
        $daftarHakAkses = [
            ['kode_hak_akses' => 'kelola_master', 'nama_hak_akses' => 'Kelola Master Data', 'modul' => 'Master Data', 'deskripsi' => 'Akses CRUD kategori, vendor, dan pengaturan sistem.'],
            ['kode_hak_akses' => 'kelola_organisasi', 'nama_hak_akses' => 'Kelola Organisasi', 'modul' => 'Organisasi', 'deskripsi' => 'Mengelola hierarki kantor cabang dan divisi.'],
            ['kode_hak_akses' => 'kelola_pengguna', 'nama_hak_akses' => 'Kelola Pengguna', 'modul' => 'Pengguna', 'deskripsi' => 'Membuat dan mengatur hak akses akun staf dan manajer.'],
            ['kode_hak_akses' => 'lihat_aset', 'nama_hak_akses' => 'Melihat Daftar Aset', 'modul' => 'Aset', 'deskripsi' => 'Melihat informasi inventaris aset sesuai unit kerja.'],
            ['kode_hak_akses' => 'tambah_aset', 'nama_hak_akses' => 'Registrasi Aset Baru', 'modul' => 'Aset', 'deskripsi' => 'Mendaftarkan aset baru milik perusahaan atau vendor.'],
            ['kode_hak_akses' => 'ubah_aset', 'nama_hak_akses' => 'Ubah Data Aset', 'modul' => 'Aset', 'deskripsi' => 'Memperbarui data dan spesifikasi teknis aset.'],
            ['kode_hak_akses' => 'hapus_aset', 'nama_hak_akses' => 'Hapus / Nonaktifkan Aset', 'modul' => 'Aset', 'deskripsi' => 'Menonaktifkan atau menghapus aset sesuai prosedur.'],
            ['kode_hak_akses' => 'buat_penugasan', 'nama_hak_akses' => 'Buat Penugasan Staf', 'modul' => 'Penugasan', 'deskripsi' => 'Manajer menugaskan pekerjaan kepada staf teknisi.'],
            ['kode_hak_akses' => 'validasi_penugasan', 'nama_hak_akses' => 'Validasi Pekerjaan Staf', 'modul' => 'Penugasan', 'deskripsi' => 'Manajer memverifikasi hasil kerja dan bukti foto.'],
            ['kode_hak_akses' => 'laksanakan_tugas', 'nama_hak_akses' => 'Laksanakan Tugas', 'modul' => 'Penugasan', 'deskripsi' => 'Staf mengunggah bukti dan melaporkan kondisi aset.'],
            ['kode_hak_akses' => 'ajukan_pengadaan', 'nama_hak_akses' => 'Ajukan Pengadaan', 'modul' => 'Pengadaan', 'deskripsi' => 'Membuat proposal pengadaan barang dan estimasi biaya.'],
            ['kode_hak_akses' => 'setujui_pengadaan', 'nama_hak_akses' => 'Setujui Pengadaan', 'modul' => 'Pengadaan', 'deskripsi' => 'Otoritas Kepala Kantor menyetujui biaya pengadaan.'],
            ['kode_hak_akses' => 'buat_bast', 'nama_hak_akses' => 'Buat Draft BAST', 'modul' => 'BAST', 'deskripsi' => 'Menyusun berita acara serah terima aset.'],
            ['kode_hak_akses' => 'setujui_bast', 'nama_hak_akses' => 'Setujui & Sahkan BAST', 'modul' => 'BAST', 'deskripsi' => 'Verifikasi dan pengesahan dokumen BAST resmi.'],
            ['kode_hak_akses' => 'ekspor_laporan', 'nama_hak_akses' => 'Ekspor Laporan PDF', 'modul' => 'Laporan', 'deskripsi' => 'Mengunduh dan mencetak laporan formal administrasi aset.'],
        ];
        foreach ($daftarHakAkses as $ha) {
            HakAkses::create($ha);
        }

        // Hubungkan Peran Admin dengan semua Hak Akses
        $semuaHak = HakAkses::pluck('id')->toArray();
        $peranMap['admin']->hakAkses()->sync($semuaHak);

        // 3. SEED KANTOR (10 records - Hierarki Bertingkat)
        $kantorPusat = Kantor::create([
            'kode_kantor' => '17000',
            'nama_kantor' => 'Kantor Pusat Jakarta',
            'jenis_kantor' => 'pusat',
            'induk_kantor_id' => null,
            'alamat' => 'Jl. Jenderal Sudirman Kav. 52-53, Jakarta Selatan',
            'nomor_telepon' => '021-5150001',
            'email' => 'pusat@siaset.com',
            'status_aktif' => true,
        ]);

        $daftarCabang = [
            ['kode_kantor' => '17101', 'nama_kantor' => 'Kantor Cabang Bandung', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Asia Afrika No. 120, Bandung', 'nomor_telepon' => '022-4200001', 'email' => 'bandung@siaset.com'],
            ['kode_kantor' => '17102', 'nama_kantor' => 'Kantor Cabang Surabaya', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Pemuda No. 45, Surabaya', 'nomor_telepon' => '031-5310002', 'email' => 'surabaya@siaset.com'],
            ['kode_kantor' => '17103', 'nama_kantor' => 'Kantor Cabang Semarang', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Pahlawan No. 8, Semarang', 'nomor_telepon' => '024-8310003', 'email' => 'semarang@siaset.com'],
            ['kode_kantor' => '17104', 'nama_kantor' => 'Kantor Cabang Medan', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Balai Kota No. 1, Medan', 'nomor_telepon' => '061-4510004', 'email' => 'medan@siaset.com'],
            ['kode_kantor' => '17105', 'nama_kantor' => 'Kantor Cabang Makassar', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Urip Sumoharjo No. 88, Makassar', 'nomor_telepon' => '0411-441005', 'email' => 'makassar@siaset.com'],
            ['kode_kantor' => '17106', 'nama_kantor' => 'Kantor Cabang Yogyakarta', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Malioboro No. 56, Yogyakarta', 'nomor_telepon' => '0274-561006', 'email' => 'jogja@siaset.com'],
            ['kode_kantor' => '17107', 'nama_kantor' => 'Kantor Cabang Denpasar', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Teuku Umar No. 22, Denpasar', 'nomor_telepon' => '0361-221007', 'email' => 'denpasar@siaset.com'],
            ['kode_kantor' => '17108', 'nama_kantor' => 'Kantor Cabang Palembang', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Sudirman No. 102, Palembang', 'nomor_telepon' => '0711-351008', 'email' => 'palembang@siaset.com'],
            ['kode_kantor' => '17113', 'nama_kantor' => 'Kantor Cabang Bekasi Pasar Lama', 'jenis_kantor' => 'cabang', 'alamat' => 'Jl. Ir. H. Juanda No. 88, Bekasi', 'nomor_telepon' => '021-8840113', 'email' => 'bekasi@siaset.com'],
        ];

        $kantorMap = ['17000' => $kantorPusat];
        foreach ($daftarCabang as $cb) {
            $cb['induk_kantor_id'] = $kantorPusat->id;
            $cb['status_aktif'] = true;
            $kantorMap[$cb['kode_kantor']] = Kantor::create($cb);
        }

        // 4. SEED DIVISI (10 records)
        $daftarDivisi = [
            ['kode_divisi' => '17001', 'nama_divisi' => 'Bagian Pemasaran & Penjualan', 'kantor_id' => $kantorPusat->id],
            ['kode_divisi' => '17002', 'nama_divisi' => 'Divisi Teknologi Informasi & Jaringan', 'kantor_id' => $kantorPusat->id],
            ['kode_divisi' => '17003', 'nama_divisi' => 'Divisi Keuangan & Akuntansi', 'kantor_id' => $kantorPusat->id],
            ['kode_divisi' => '17004', 'nama_divisi' => 'Divisi SDM & Operasional Umum', 'kantor_id' => $kantorPusat->id],
            ['kode_divisi' => '17005', 'nama_divisi' => 'Divisi Logistik & Pengelolaan Aset', 'kantor_id' => $kantorPusat->id],
            ['kode_divisi' => '17111', 'nama_divisi' => 'Bagian Pelayanan Operasional Bekasi', 'kantor_id' => $kantorMap['17113']->id],
            ['kode_divisi' => '17112', 'nama_divisi' => 'Bagian Keuangan & Administrasi Bekasi', 'kantor_id' => $kantorMap['17113']->id],
            ['kode_divisi' => '17201', 'nama_divisi' => 'Bagian Operasional Cabang Bandung', 'kantor_id' => $kantorMap['17101']->id],
            ['kode_divisi' => '17301', 'nama_divisi' => 'Bagian Operasional Cabang Surabaya', 'kantor_id' => $kantorMap['17102']->id],
            ['kode_divisi' => '17401', 'nama_divisi' => 'Bagian Operasional Cabang Medan', 'kantor_id' => $kantorMap['17104']->id],
        ];
        $divisiMap = [];
        foreach ($daftarDivisi as $d) {
            $d['status_aktif'] = true;
            $divisiMap[$d['kode_divisi']] = Divisi::create($d);
        }

        // 5. SEED PENGGUNA (10 records - mewakili seluruh peran)
        $daftarPengguna = [
            [
                'nip' => '19850101001',
                'nama' => 'Rahmat Hidayat (Admin)',
                'email' => 'admin@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456701',
                'peran_id' => $peranMap['admin']->id,
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17002']->id,
            ],
            [
                'nip' => '19860202002',
                'nama' => 'Ir. Hendra Gunawan (Kepala Pusat)',
                'email' => 'kepala.pusat@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456702',
                'peran_id' => $peranMap['kepala_kantor']->id,
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => null,
            ],
            [
                'nip' => '19870303003',
                'nama' => 'Drs. Bambang Sudiro (Kepala Bekasi)',
                'email' => 'kepala.bekasi@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456703',
                'peran_id' => $peranMap['kepala_kantor']->id,
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => null,
            ],
            [
                'nip' => '19880404004',
                'nama' => 'Ahmad Fauzi, S.T. (Manajer Aset Pusat)',
                'email' => 'manajer.aset@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456704',
                'peran_id' => $peranMap['manajer']->id,
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17005']->id,
            ],
            [
                'nip' => '19890505005',
                'nama' => 'Dewi Sartika, S.E. (Manajer Cabang Bekasi)',
                'email' => 'manajer.bekasi@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456705',
                'peran_id' => $peranMap['manajer']->id,
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
            ],
            [
                'nip' => '19920606006',
                'nama' => 'Budi Santoso (Staf Teknisi Pusat)',
                'email' => 'staf.budi@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456706',
                'peran_id' => $peranMap['staf']->id,
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17002']->id,
            ],
            [
                'nip' => '19930707007',
                'nama' => 'Andi Pratama (Staf Operasional Bekasi)',
                'email' => 'staf.andi@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456707',
                'peran_id' => $peranMap['staf']->id,
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
            ],
            [
                'nip' => '19940808008',
                'nama' => 'Citra Lestari (Staf Inventaris)',
                'email' => 'staf.citra@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456708',
                'peran_id' => $peranMap['staf']->id,
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17005']->id,
            ],
            [
                'nip' => '19950909009',
                'nama' => 'Dani Ramadhan (Staf Logistik Bekasi)',
                'email' => 'staf.dani@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456709',
                'peran_id' => $peranMap['staf']->id,
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
            ],
            [
                'nip' => '19911010010',
                'nama' => 'Eko Prasetyo, M.Ak. (Auditor Internal)',
                'email' => 'auditor.eko@siaset.com',
                'kata_sandi' => Hash::make('password123'),
                'nomor_telepon' => '08123456710',
                'peran_id' => $peranMap['auditor_internal']->id,
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17003']->id,
            ],
        ];

        $penggunaMap = [];
        foreach ($daftarPengguna as $usr) {
            $usr['status_aktif'] = true;
            $penggunaMap[$usr['nip']] = Pengguna::create($usr);
        }

        // 6. SEED KATEGORI ASET (10 records)
        $daftarKategori = [
            ['kode_kategori' => 'KAT-01', 'nama_kategori' => 'Laptop & Komputer Portabel', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 48, 'persentase_residu' => 10.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Komputer jinjing operasional karyawan dan manajemen.'],
            ['kode_kategori' => 'KAT-02', 'nama_kategori' => 'Desktop PC & Workstation', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 60, 'persentase_residu' => 5.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Komputer meja di kantor pusat dan kantor cabang.'],
            ['kode_kategori' => 'KAT-03', 'nama_kategori' => 'Perangkat Jaringan & Server', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 60, 'persentase_residu' => 5.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Router, switch manageable, access point, dan server rack.'],
            ['kode_kategori' => 'KAT-04', 'nama_kategori' => 'Kendaraan Operasional Roda 4', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 96, 'persentase_residu' => 20.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Mobil dinas dan kendaraan logistik perusahaan.'],
            ['kode_kategori' => 'KAT-05', 'nama_kategori' => 'Furnitur & Meja Kantor', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 96, 'persentase_residu' => 10.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Meja kerja, kursi ergonomis, partisi, dan lemari arsip.'],
            ['kode_kategori' => 'KAT-06', 'nama_kategori' => 'Printer Multifungsi & Scanner', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 48, 'persentase_residu' => 5.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Mesin cetak dokumen dan pemindai lembar berkas.'],
            ['kode_kategori' => 'KAT-07', 'nama_kategori' => 'Mesin Fotokopi Sewa (Vendor)', 'tipe_aset' => 'sewa', 'masa_manfaat_bulan' => 36, 'persentase_residu' => 0.00, 'metode_penyusutan_default' => 'tanpa_penyusutan', 'deskripsi' => 'Mesin fotokopi kontrak sewa dengan pemeliharaan pihak ketiga.'],
            ['kode_kategori' => 'KAT-08', 'nama_kategori' => 'Router Enterprise Sewa (ISP)', 'tipe_aset' => 'sewa', 'masa_manfaat_bulan' => 24, 'persentase_residu' => 0.00, 'metode_penyusutan_default' => 'tanpa_penyusutan', 'deskripsi' => 'Perangkat koneksi leased line dari penyedia internet.'],
            ['kode_kategori' => 'KAT-09', 'nama_kategori' => 'Genset & Backup Power', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 120, 'persentase_residu' => 15.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Generator listrik cadangan kantor pusat dan cabang utama.'],
            ['kode_kategori' => 'KAT-10', 'nama_kategori' => 'Pendingin Ruangan (AC Standing/Split)', 'tipe_aset' => 'perusahaan', 'masa_manfaat_bulan' => 60, 'persentase_residu' => 5.00, 'metode_penyusutan_default' => 'garis_lurus', 'deskripsi' => 'Sistem pendingin udara ruang server dan ruang kerja.'],
        ];
        $kategoriMap = [];
        foreach ($daftarKategori as $kat) {
            $kategoriMap[$kat['kode_kategori']] = KategoriAset::create($kat);
        }

        // 7. SEED VENDOR (10 records)
        $daftarVendor = [
            ['kode_vendor' => 'VND-01', 'nama_vendor' => 'PT Sentra Dinamika Teknologi', 'kontak_person' => 'Bambang Irawan', 'nomor_telepon' => '021-5790111', 'email' => 'sales@sentradinamika.co.id', 'alamat' => 'Gedung Menara Mulia Lt. 15, Jl. Gatot Subroto, Jakarta'],
            ['kode_vendor' => 'VND-02', 'nama_vendor' => 'PT Metrodata Electronics Tbk', 'kontak_person' => 'Siska Wulandari', 'nomor_telepon' => '021-2934500', 'email' => 'support@metrodata.co.id', 'alamat' => 'Wisma Keiai Lt. 16, Jl. Jend. Sudirman, Jakarta'],
            ['kode_vendor' => 'VND-03', 'nama_vendor' => 'CV Sukses Mandiri Furniture', 'kontak_person' => 'Agus Priyono', 'nomor_telepon' => '021-8241234', 'email' => 'suksesmandiri@gmail.com', 'alamat' => 'Kawasan Industri Narogong KM 12, Bekasi'],
            ['kode_vendor' => 'VND-04', 'nama_vendor' => 'PT Astra International Tbk (Auto2000)', 'kontak_person' => 'Dwi Yanto', 'nomor_telepon' => '021-6530000', 'email' => 'fleet@auto2000.co.id', 'alamat' => 'Jl. Gaya Motor III No. 3, Sunter II, Jakarta Utara'],
            ['kode_vendor' => 'VND-05', 'nama_vendor' => 'PT Telkom Akses', 'kontak_person' => 'Rudy Hartono', 'nomor_telepon' => '021-8080147', 'email' => 'enterprise@telkomakses.co.id', 'alamat' => 'Jl. S. Parman Kav. 8, Slipi, Jakarta Barat'],
            ['kode_vendor' => 'VND-06', 'nama_vendor' => 'PT Astra Graphia Tbk (Fuji Xerox)', 'kontak_person' => 'Maya Indah', 'nomor_telepon' => '021-3909190', 'email' => 'rental@astragraphia.co.id', 'alamat' => 'Jl. Kramat Raya No. 43, Senen, Jakarta Pusat'],
            ['kode_vendor' => 'VND-07', 'nama_vendor' => 'CV Berkah Solusi Pendingin', 'kontak_person' => 'Wahyu Hidayat', 'nomor_telepon' => '021-8899771', 'email' => 'berkahac@yahoo.com', 'alamat' => 'Jl. Cut Meutia No. 19, Bekasi Timur'],
            ['kode_vendor' => 'VND-08', 'nama_vendor' => 'PT Mega Daya Genset Indonesia', 'kontak_person' => 'Surya Wijaya', 'nomor_telepon' => '021-5566778', 'email' => 'service@megadayagenset.co.id', 'alamat' => 'Jl. Daan Mogot KM 14, Cengkareng, Jakarta Barat'],
            ['kode_vendor' => 'VND-09', 'nama_vendor' => 'PT Mitra Integrasi Informatika (MII)', 'kontak_person' => 'Rina Marlina', 'nomor_telepon' => '021-2934555', 'email' => 'corporate@mii.co.id', 'alamat' => 'APL Tower Lt. 37, Podomoro City, Jakarta Barat'],
            ['kode_vendor' => 'VND-10', 'nama_vendor' => 'PT Global Solusi Rental Nusantara', 'kontak_person' => 'Taufik Akbar', 'nomor_telepon' => '021-7890123', 'email' => 'info@globalsolusirental.com', 'alamat' => 'Jl. TB Simatupang No. 70, Pasar Minggu, Jakarta Selatan'],
        ];
        $vendorMap = [];
        foreach ($daftarVendor as $vnd) {
            $vnd['status_aktif'] = true;
            $vendorMap[$vnd['kode_vendor']] = Vendor::create($vnd);
        }

        // 8. SEED KONTRAK SEWA (10 records)
        $daftarKontrak = [
            [
                'nomor_kontrak' => 'KTR/VND-06/2026/001',
                'vendor_id' => $vendorMap['VND-06']->id,
                'judul_kontrak' => 'Sewa Mesin Fotokopi Multifungsi Kantor Pusat',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'biaya_sewa' => 24000000.00,
                'periode_pembayaran' => 'bulanan',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Maintenance bulanan rutin dan penggantian toner ditanggung vendor.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-06/2026/002',
                'vendor_id' => $vendorMap['VND-06']->id,
                'judul_kontrak' => 'Sewa Mesin Fotokopi Kantor Cabang Bekasi',
                'tanggal_mulai' => '2026-02-01',
                'tanggal_selesai' => '2027-01-31',
                'biaya_sewa' => 18000000.00,
                'periode_pembayaran' => 'bulanan',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Vendor menyediakan teknisi on-call maksimal 4 jam kerja.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-05/2026/003',
                'vendor_id' => $vendorMap['VND-05']->id,
                'judul_kontrak' => 'Sewa Router Cisco Leased Line Pusat',
                'tanggal_mulai' => '2025-06-01',
                'tanggal_selesai' => '2027-05-31',
                'biaya_sewa' => 36000000.00,
                'periode_pembayaran' => 'kuartal',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'SLA jaringan 99.8% dan router sparepart ditanggung Telkom.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-05/2026/004',
                'vendor_id' => $vendorMap['VND-05']->id,
                'judul_kontrak' => 'Sewa Router SD-WAN Cabang Bekasi',
                'tanggal_mulai' => '2026-01-15',
                'tanggal_selesai' => '2027-01-14',
                'biaya_sewa' => 15000000.00,
                'periode_pembayaran' => 'bulanan',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Monitoring uptime 24/7 dan hardware swap 1x24 jam.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-10/2026/005',
                'vendor_id' => $vendorMap['VND-10']->id,
                'judul_kontrak' => 'Sewa Laptop Proyek Audit Internal',
                'tanggal_mulai' => '2026-03-01',
                'tanggal_selesai' => '2026-08-31',
                'biaya_sewa' => 12000000.00,
                'periode_pembayaran' => 'bulanan',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Unit cadangan disiapkan di hari yang sama jika rusak.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-08/2026/006',
                'vendor_id' => $vendorMap['VND-08']->id,
                'judul_kontrak' => 'Kontrak Pemeliharaan Rutin Genset Cummins 150kVA',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'biaya_sewa' => 28000000.00,
                'periode_pembayaran' => 'semester',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Pengecekan oli, filter, baterai, dan uji beban bulanan.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-07/2026/007',
                'vendor_id' => $vendorMap['VND-07']->id,
                'judul_kontrak' => 'Kontrak Servis Berkala AC Kantor Cabang Bekasi',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'biaya_sewa' => 8400000.00,
                'periode_pembayaran' => 'kuartal',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Pembersihan evaporator, kondensor, dan isi freon per 3 bulan.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-06/2025/008',
                'vendor_id' => $vendorMap['VND-06']->id,
                'judul_kontrak' => 'Sewa Mesin Fotokopi Cabang Bandung (Mendekati Berakhir)',
                'tanggal_mulai' => '2025-05-01',
                'tanggal_selesai' => '2026-11-01',
                'biaya_sewa' => 18000000.00,
                'periode_pembayaran' => 'bulanan',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Masa evaluasi perpanjangan kontrak 30 hari sebelum berakhir.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-05/2025/009',
                'vendor_id' => $vendorMap['VND-05']->id,
                'judul_kontrak' => 'Sewa Router Cabang Surabaya',
                'tanggal_mulai' => '2025-01-01',
                'tanggal_selesai' => '2026-12-31',
                'biaya_sewa' => 14000000.00,
                'periode_pembayaran' => 'tahunan',
                'status_kontrak' => 'aktif',
                'ketentuan_pemeliharaan' => 'Penggantian suku cadang oleh Telkom.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
            [
                'nomor_kontrak' => 'KTR/VND-10/2025/010',
                'vendor_id' => $vendorMap['VND-10']->id,
                'judul_kontrak' => 'Sewa Scanner Dokumen Arsip Bekasi (Telah Selesai)',
                'tanggal_mulai' => '2025-01-01',
                'tanggal_selesai' => '2025-12-31',
                'biaya_sewa' => 6000000.00,
                'periode_pembayaran' => 'semester',
                'status_kontrak' => 'berakhir',
                'ketentuan_pemeliharaan' => 'Kontrak telah selesai dan unit telah dikembalikan.',
                'penanggung_jawab_pemeliharaan' => 'vendor',
            ],
        ];
        $kontrakMap = [];
        foreach ($daftarKontrak as $k) {
            $kontrakMap[$k['nomor_kontrak']] = KontrakSewa::create($k);
        }

        // 9. SEED ASET (10 records: 7 Milik Sendiri, 3 Sewa Vendor)
        $daftarAset = [
            [
                'nomor_index' => 'AST/17000/202610/00001',
                'nomor_seri' => 'LNV-TP-T14-00192',
                'barcode_vendor' => '192837465011',
                'nama_aset' => 'ThinkPad T14 Gen 4 Intel Core i7',
                'kategori_aset_id' => $kategoriMap['KAT-01']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-01']->id,
                'merk' => 'Lenovo',
                'tipe_model' => 'ThinkPad T14 Gen 4',
                'kondisi' => 'sangat_baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17002']->id,
                'penanggung_jawab_id' => $penggunaMap['19920606006']->id,
                'tanggal_perolehan' => '2025-01-15',
                'sumber_pengadaan' => 'Pengadaan Rutin IT 2025',
                'nilai_perolehan' => 22000000.00,
                'nilai_residu' => 2200000.00,
                'masa_manfaat_bulan' => 48,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2025-02-01',
                'nilai_buku_saat_ini' => 14162500.00,
                'akumulasi_penyusutan' => 7837500.00,
                'spesifikasi_teknis' => 'Core i7-1365U, 32GB DDR5, 1TB NVMe, 14 Inch IPS WUXGA',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17113/202610/00002',
                'nomor_seri' => 'DELL-LAT-5440-881',
                'barcode_vendor' => '192837465012',
                'nama_aset' => 'Dell Latitude 5440 Core i5',
                'kategori_aset_id' => $kategoriMap['KAT-01']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-02']->id,
                'merk' => 'Dell',
                'tipe_model' => 'Latitude 5440',
                'kondisi' => 'baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
                'penanggung_jawab_id' => $penggunaMap['19930707007']->id,
                'tanggal_perolehan' => '2025-03-10',
                'sumber_pengadaan' => 'Pengadaan Cabang Bekasi',
                'nilai_perolehan' => 16500000.00,
                'nilai_residu' => 1650000.00,
                'masa_manfaat_bulan' => 48,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2025-04-01',
                'nilai_buku_saat_ini' => 11090625.00,
                'akumulasi_penyusutan' => 5409375.00,
                'spesifikasi_teknis' => 'Core i5-1335U, 16GB RAM, 512GB SSD, Windows 11 Pro',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17000/202610/00003',
                'nomor_seri' => 'SRV-DL-R750-001',
                'barcode_vendor' => '192837465013',
                'nama_aset' => 'Dell PowerEdge R750 Rack Server',
                'kategori_aset_id' => $kategoriMap['KAT-03']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-02']->id,
                'merk' => 'Dell PowerEdge',
                'tipe_model' => 'R750 2U Rack',
                'kondisi' => 'sangat_baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17002']->id,
                'penanggung_jawab_id' => $penggunaMap['19850101001']->id,
                'tanggal_perolehan' => '2024-06-01',
                'sumber_pengadaan' => 'Proyek Modernisasi Data Center Pusat',
                'nilai_perolehan' => 145000000.00,
                'nilai_residu' => 7250000.00,
                'masa_manfaat_bulan' => 60,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2024-07-01',
                'nilai_buku_saat_ini' => 82991666.00,
                'akumulasi_penyusutan' => 62008334.00,
                'spesifikasi_teknis' => 'Dual Intel Xeon Gold 6330, 128GB ECC RAM, 4x 1.92TB Enterprise SSD RAID 10',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17113/202610/00004',
                'nomor_seri' => 'CS-C9200L-48P-02',
                'barcode_vendor' => '192837465014',
                'nama_aset' => 'Cisco Catalyst 9200L 48-Port PoE+',
                'kategori_aset_id' => $kategoriMap['KAT-03']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-01']->id,
                'merk' => 'Cisco Systems',
                'tipe_model' => 'Catalyst 9200L-48P-4G',
                'kondisi' => 'baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
                'penanggung_jawab_id' => $penggunaMap['19930707007']->id,
                'tanggal_perolehan' => '2024-09-15',
                'sumber_pengadaan' => 'Upgrade Jaringan Cabang',
                'nilai_perolehan' => 48000000.00,
                'nilai_residu' => 2400000.00,
                'masa_manfaat_bulan' => 60,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2024-10-01',
                'nilai_buku_saat_ini' => 32800000.00,
                'akumulasi_penyusutan' => 15200000.00,
                'spesifikasi_teknis' => '48 Ports Gigabit PoE+ 740W Budget, 4x 1G SFP Uplinks, Network Essentials',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17000/202610/00005',
                'nomor_seri' => 'TYT-INNV-B1234XYZ',
                'barcode_vendor' => '192837465015',
                'nama_aset' => 'Toyota Kijang Innova Zenix 2.0 V CVT',
                'kategori_aset_id' => $kategoriMap['KAT-04']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-04']->id,
                'merk' => 'Toyota',
                'tipe_model' => 'Innova Zenix 2.0 V',
                'kondisi' => 'sangat_baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17004']->id,
                'penanggung_jawab_id' => $penggunaMap['19860202002']->id,
                'tanggal_perolehan' => '2024-01-20',
                'sumber_pengadaan' => 'Kendaraan Dinas Manajemen Pusat',
                'nilai_perolehan' => 475000000.00,
                'nilai_residu' => 95000000.00,
                'masa_manfaat_bulan' => 96,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2024-02-01',
                'nilai_buku_saat_ini' => 366250000.00,
                'akumulasi_penyusutan' => 108750000.00,
                'spesifikasi_teknis' => 'Bensin 2000cc, Plat B 1234 XYZ, Warna Hitam Metalik, Tahun 2024',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17113/202610/00006',
                'nomor_seri' => 'TYT-AVZ-B5678OPQ',
                'barcode_vendor' => '192837465016',
                'nama_aset' => 'Toyota Avanza 1.5 G CVT Operasional Bekasi',
                'kategori_aset_id' => $kategoriMap['KAT-04']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-04']->id,
                'merk' => 'Toyota',
                'tipe_model' => 'Avanza 1.5 G CVT',
                'kondisi' => 'baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
                'penanggung_jawab_id' => $penggunaMap['19870303003']->id,
                'tanggal_perolehan' => '2024-03-12',
                'sumber_pengadaan' => 'Kendaraan Operasional Cabang Bekasi',
                'nilai_perolehan' => 260000000.00,
                'nilai_residu' => 52000000.00,
                'masa_manfaat_bulan' => 96,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2024-04-01',
                'nilai_buku_saat_ini' => 203666667.00,
                'akumulasi_penyusutan' => 56333333.00,
                'spesifikasi_teknis' => '1500cc, Plat B 5678 OPQ, Warna Silver Metalik, Tahun 2024',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17000/202610/00007',
                'nomor_seri' => 'DAIKIN-VRV-5PK-01',
                'barcode_vendor' => '192837465017',
                'nama_aset' => 'Daikin AC Standing Floor 5 PK Ruang Server',
                'kategori_aset_id' => $kategoriMap['KAT-10']->id,
                'jenis_kepemilikan' => 'milik_sendiri',
                'kontrak_sewa_id' => null,
                'vendor_id' => $vendorMap['VND-07']->id,
                'merk' => 'Daikin',
                'tipe_model' => 'FVA125AMVM Standing 5 PK',
                'kondisi' => 'rusak_ringan',
                'status_operasional' => 'dalam_pemeliharaan',
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17002']->id,
                'penanggung_jawab_id' => $penggunaMap['19920606006']->id,
                'tanggal_perolehan' => '2023-05-10',
                'sumber_pengadaan' => 'Fasilitas Server Pusat',
                'nilai_perolehan' => 38000000.00,
                'nilai_residu' => 1900000.00,
                'masa_manfaat_bulan' => 60,
                'metode_penyusutan' => 'garis_lurus',
                'tanggal_mulai_penyusutan' => '2023-06-01',
                'nilai_buku_saat_ini' => 13331667.00,
                'akumulasi_penyusutan' => 24668333.00,
                'spesifikasi_teknis' => 'Kapasitas 45.000 BTU, Refrigerant R32, 3 Phase',
                'status_inventaris' => 'ditemukan',
            ],
            // Aset Sewa Vendor (3 records)
            [
                'nomor_index' => 'AST/17000/202610/00008',
                'nomor_seri' => 'FX-APEOS-C3570-01',
                'barcode_vendor' => 'FX-JKT-2026-991',
                'nama_aset' => 'Fuji Xerox ApeosPort C3570 (Sewa Vendor)',
                'kategori_aset_id' => $kategoriMap['KAT-07']->id,
                'jenis_kepemilikan' => 'sewa_vendor',
                'kontrak_sewa_id' => $kontrakMap['KTR/VND-06/2026/001']->id,
                'vendor_id' => $vendorMap['VND-06']->id,
                'merk' => 'Fuji Xerox',
                'tipe_model' => 'ApeosPort C3570 Multifunction',
                'kondisi' => 'sangat_baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17004']->id,
                'penanggung_jawab_id' => $penggunaMap['19880404004']->id,
                'tanggal_perolehan' => '2026-01-05',
                'sumber_pengadaan' => 'Kontrak Sewa KTR/VND-06/2026/001',
                'nilai_perolehan' => 0.00,
                'nilai_residu' => 0.00,
                'masa_manfaat_bulan' => 36,
                'metode_penyusutan' => 'tanpa_penyusutan',
                'tanggal_mulai_penyusutan' => null,
                'nilai_buku_saat_ini' => 0.00,
                'akumulasi_penyusutan' => 0.00,
                'spesifikasi_teknis' => 'Color Copy/Print/Scan 35 ppm, Duplex ADF, Network Gigabit & NFC',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17113/202610/00009',
                'nomor_seri' => 'FX-APEOS-C3070-02',
                'barcode_vendor' => 'FX-BKS-2026-882',
                'nama_aset' => 'Fuji Xerox ApeosPort C3070 Bekasi (Sewa Vendor)',
                'kategori_aset_id' => $kategoriMap['KAT-07']->id,
                'jenis_kepemilikan' => 'sewa_vendor',
                'kontrak_sewa_id' => $kontrakMap['KTR/VND-06/2026/002']->id,
                'vendor_id' => $vendorMap['VND-06']->id,
                'merk' => 'Fuji Xerox',
                'tipe_model' => 'ApeosPort C3070 Multifunction',
                'kondisi' => 'baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorMap['17113']->id,
                'divisi_id' => $divisiMap['17111']->id,
                'penanggung_jawab_id' => $penggunaMap['19890505005']->id,
                'tanggal_perolehan' => '2026-02-05',
                'sumber_pengadaan' => 'Kontrak Sewa KTR/VND-06/2026/002',
                'nilai_perolehan' => 0.00,
                'nilai_residu' => 0.00,
                'masa_manfaat_bulan' => 36,
                'metode_penyusutan' => 'tanpa_penyusutan',
                'tanggal_mulai_penyusutan' => null,
                'nilai_buku_saat_ini' => 0.00,
                'akumulasi_penyusutan' => 0.00,
                'spesifikasi_teknis' => 'Color Copy/Print/Scan 30 ppm, Finisher Stapler, Tray 4x500 sheets',
                'status_inventaris' => 'ditemukan',
            ],
            [
                'nomor_index' => 'AST/17000/202610/00010',
                'nomor_seri' => 'CS-ISR-4431-01',
                'barcode_vendor' => 'TLK-RTR-JKT-101',
                'nama_aset' => 'Cisco ISR 4431 Gateway Leased Line (Sewa Telkom)',
                'kategori_aset_id' => $kategoriMap['KAT-08']->id,
                'jenis_kepemilikan' => 'sewa_vendor',
                'kontrak_sewa_id' => $kontrakMap['KTR/VND-05/2026/003']->id,
                'vendor_id' => $vendorMap['VND-05']->id,
                'merk' => 'Cisco',
                'tipe_model' => 'ISR 4431 Router',
                'kondisi' => 'sangat_baik',
                'status_operasional' => 'aktif',
                'kantor_id' => $kantorPusat->id,
                'divisi_id' => $divisiMap['17002']->id,
                'penanggung_jawab_id' => $penggunaMap['19850101001']->id,
                'tanggal_perolehan' => '2025-06-05',
                'sumber_pengadaan' => 'Kontrak Telkom KTR/VND-05/2026/003',
                'nilai_perolehan' => 0.00,
                'nilai_residu' => 0.00,
                'masa_manfaat_bulan' => 24,
                'metode_penyusutan' => 'tanpa_penyusutan',
                'tanggal_mulai_penyusutan' => null,
                'nilai_buku_saat_ini' => 0.00,
                'akumulasi_penyusutan' => 0.00,
                'spesifikasi_teknis' => 'Throughput 500Mbps-1Gbps, Dual Power Supply, SEC License',
                'status_inventaris' => 'ditemukan',
            ],
        ];

        $asetMap = [];
        foreach ($daftarAset as $ast) {
            $createdAset = Aset::create($ast);
            $asetMap[$createdAset->nomor_index] = $createdAset;

            // Catat audit trail registrasi awal
            RiwayatAset::create([
                'aset_id' => $createdAset->id,
                'pengguna_id' => $createdAset->penanggung_jawab_id,
                'jenis_aktivitas' => 'registrasi',
                'kantor_tujuan_id' => $createdAset->kantor_id,
                'divisi_tujuan_id' => $createdAset->divisi_id,
                'kondisi_sebelumnya' => null,
                'kondisi_sesudahnya' => $createdAset->kondisi,
                'status_sebelumnya' => null,
                'status_sesudahnya' => $createdAset->status_operasional,
                'keterangan' => 'Registrasi perdana aset ke sistem SI-ASET.',
                'ip_address' => '127.0.0.1',
            ]);
        }

        // Set penomoran counter untuk prefix AST
        PenomoranCounter::create([
            'prefix' => 'AST',
            'tahun' => 2026,
            'bulan' => 10,
            'nomor_terakhir' => 10,
        ]);

        // 10. SEED JADWAL PEMELIHARAAN (10 records)
        $jadwalList = [
            ['aset_idx' => 'AST/17000/202610/00001', 'jenis' => 'preventif', 'interval' => 90, 'terakhir' => '2026-07-15', 'berikutnya' => '2026-10-15', 'biaya' => 250000.00, 'status' => 'mendekati_tenggat'],
            ['aset_idx' => 'AST/17113/202610/00002', 'jenis' => 'preventif', 'interval' => 90, 'terakhir' => '2026-08-10', 'berikutnya' => '2026-11-10', 'biaya' => 250000.00, 'status' => 'terjadwal'],
            ['aset_idx' => 'AST/17000/202610/00003', 'jenis' => 'rutin', 'interval' => 30, 'terakhir' => '2026-09-20', 'berikutnya' => '2026-10-20', 'biaya' => 1500000.00, 'status' => 'mendekati_tenggat'],
            ['aset_idx' => 'AST/17113/202610/00004', 'jenis' => 'rutin', 'interval' => 60, 'terakhir' => '2026-08-25', 'berikutnya' => '2026-10-25', 'biaya' => 500000.00, 'status' => 'terjadwal'],
            ['aset_idx' => 'AST/17000/202610/00005', 'jenis' => 'rutin', 'interval' => 180, 'terakhir' => '2026-04-10', 'berikutnya' => '2026-10-10', 'biaya' => 2500000.00, 'status' => 'terlambat'],
            ['aset_idx' => 'AST/17113/202610/00006', 'jenis' => 'rutin', 'interval' => 180, 'terakhir' => '2026-05-15', 'berikutnya' => '2026-11-15', 'biaya' => 1800000.00, 'status' => 'terjadwal'],
            ['aset_idx' => 'AST/17000/202610/00007', 'jenis' => 'korektif', 'interval' => 30, 'terakhir' => '2026-09-01', 'berikutnya' => '2026-10-01', 'biaya' => 3500000.00, 'status' => 'terlambat'],
            ['aset_idx' => 'AST/17000/202610/00008', 'jenis' => 'rutin', 'interval' => 30, 'terakhir' => '2026-09-28', 'berikutnya' => '2026-10-28', 'biaya' => 0.00, 'status' => 'terjadwal'],
            ['aset_idx' => 'AST/17113/202610/00009', 'jenis' => 'rutin', 'interval' => 30, 'terakhir' => '2026-09-29', 'berikutnya' => '2026-10-29', 'biaya' => 0.00, 'status' => 'terjadwal'],
            ['aset_idx' => 'AST/17000/202610/00010', 'jenis' => 'rutin', 'interval' => 90, 'terakhir' => '2026-08-01', 'berikutnya' => '2026-11-01', 'biaya' => 0.00, 'status' => 'terjadwal'],
        ];
        $jadwalMap = [];
        foreach ($jadwalList as $idx => $jdw) {
            $targetAset = $asetMap[$jdw['aset_idx']];
            $jadwalMap[$idx] = JadwalPemeliharaan::create([
                'aset_id' => $targetAset->id,
                'kantor_id' => $targetAset->kantor_id,
                'jenis_pemeliharaan' => $jdw['jenis'],
                'interval_hari' => $jdw['interval'],
                'tanggal_terakhir' => $jdw['terakhir'],
                'tanggal_berikutnya' => $jdw['berikutnya'],
                'biaya_estimasi' => $jdw['biaya'],
                'status' => $jdw['status'],
            ]);
        }

        // 11. SEED PEMELIHARAAN ASET (10 records)
        $pemeliharaanList = [
            ['nomor' => 'MNT/17000/202610/0001', 'jdw' => 0, 'aset' => 'AST/17000/202610/00001', 'teknisi' => '19920606006', 'vnd' => 'VND-01', 'tgl_m' => '2026-10-12', 'tgl_s' => '2026-10-12', 'biaya' => 250000.00, 'status' => 'disetujui', 'rusak' => 'Pembersihan debu kipas pendingin dan repaste thermal grizzly.', 'tindakan' => 'Thermal paste diganti baru, suhu normal stabil 42C.'],
            ['nomor' => 'MNT/17113/202610/0002', 'jdw' => 1, 'aset' => 'AST/17113/202610/00002', 'teknisi' => '19930707007', 'vnd' => 'VND-02', 'tgl_m' => '2026-10-14', 'tgl_s' => '2026-10-14', 'biaya' => 150000.00, 'status' => 'disetujui', 'rusak' => 'Update BIOS firmware dan driver wireless.', 'tindakan' => 'Firmware sukses diupdate ke versi 1.14.0.'],
            ['nomor' => 'MNT/17000/202610/0003', 'jdw' => 2, 'aset' => 'AST/17000/202610/00003', 'teknisi' => '19920606006', 'vnd' => 'VND-02', 'tgl_m' => '2026-10-15', 'tgl_s' => '2026-10-15', 'biaya' => 1200000.00, 'status' => 'disetujui', 'rusak' => 'Pemeriksaan disk array RAID dan pembersihan filter server.', 'tindakan' => 'Seluruh drive SAS status Optimal, SMART passed.'],
            ['nomor' => 'MNT/17113/202610/0004', 'jdw' => 3, 'aset' => 'AST/17113/202610/00004', 'teknisi' => '19930707007', 'vnd' => 'VND-01', 'tgl_m' => '2026-10-16', 'tgl_s' => null, 'biaya' => 450000.00, 'status' => 'dalam_pengerjaan', 'rusak' => 'Pemeriksaan log error PoE port 12 dan 14.', 'tindakan' => 'Penggantian kabel patch cord Cat6.'],
            ['nomor' => 'MNT/17000/202610/0005', 'jdw' => 4, 'aset' => 'AST/17000/202610/00005', 'teknisi' => '19920606006', 'vnd' => 'VND-04', 'tgl_m' => '2026-10-11', 'tgl_s' => '2026-10-12', 'biaya' => 2650000.00, 'status' => 'disetujui', 'rusak' => 'Servis berkala 20.000 KM di Auto2000.', 'tindakan' => 'Ganti oli mesin TMO 0W-20, filter oli, balancing ban.'],
            ['nomor' => 'MNT/17113/202610/0006', 'jdw' => 5, 'aset' => 'AST/17113/202610/00006', 'teknisi' => '19930707007', 'vnd' => 'VND-04', 'tgl_m' => '2026-10-13', 'tgl_s' => '2026-10-13', 'biaya' => 1750000.00, 'status' => 'disetujui', 'rusak' => 'Servis berkala 10.000 KM di Auto2000 Bekasi.', 'tindakan' => 'Ganti oli mesin dan rotasi ban.'],
            ['nomor' => 'MNT/17000/202610/0007', 'jdw' => 6, 'aset' => 'AST/17000/202610/00007', 'teknisi' => '19920606006', 'vnd' => 'VND-07', 'tgl_m' => '2026-10-17', 'tgl_s' => null, 'biaya' => 3850000.00, 'status' => 'menunggu_validasi', 'rusak' => 'Kompresor indoor berisik dan hembusan udara kurang dingin.', 'tindakan' => 'Penggantian kapasitor fan motor dan pengisian freon R32.'],
            ['nomor' => 'MNT/17000/202610/0008', 'jdw' => 7, 'aset' => 'AST/17000/202610/00008', 'teknisi' => '19920606006', 'vnd' => 'VND-06', 'tgl_m' => '2026-10-05', 'tgl_s' => '2026-10-05', 'biaya' => 0.00, 'status' => 'disetujui', 'rusak' => 'Paper jam di tray 2 dan roller kotor.', 'tindakan' => 'Pembersihan pickup roller oleh teknisi Fuji Xerox.'],
            ['nomor' => 'MNT/17113/202610/0009', 'jdw' => 8, 'aset' => 'AST/17113/202610/00009', 'teknisi' => '19930707007', 'vnd' => 'VND-06', 'tgl_m' => '2026-10-06', 'tgl_s' => '2026-10-06', 'biaya' => 0.00, 'status' => 'disetujui', 'rusak' => 'Toner hitam low replacement alert.', 'tindakan' => 'Penggantian cartridge toner hitam original.'],
            ['nomor' => 'MNT/17000/202610/0010', 'jdw' => 9, 'aset' => 'AST/17000/202610/00010', 'teknisi' => '19920606006', 'vnd' => 'VND-05', 'tgl_m' => '2026-10-08', 'tgl_s' => '2026-10-08', 'biaya' => 0.00, 'status' => 'disetujui', 'rusak' => 'Pemeriksaan port SFP leased line pusat.', 'tindakan' => 'Optic attenuation diukur -14dBm, sinyal prima.'],
        ];
        foreach ($pemeliharaanList as $pm) {
            PemeliharaanAset::create([
                'nomor_pemeliharaan' => $pm['nomor'],
                'jadwal_pemeliharaan_id' => $jadwalMap[$pm['jdw']]->id,
                'aset_id' => $asetMap[$pm['aset']]->id,
                'pengguna_teknisi_id' => $penggunaMap[$pm['teknisi']]->id,
                'vendor_id' => $vendorMap[$pm['vnd']]->id,
                'tanggal_mulai' => $pm['tgl_m'],
                'tanggal_selesai' => $pm['tgl_s'],
                'biaya_realisasi' => $pm['biaya'],
                'deskripsi_kerusakan' => $pm['rusak'],
                'tindakan_perbaikan' => $pm['tindakan'],
                'status' => $pm['status'],
            ]);
        }

        // 12. SEED PENUGASAN STAF (10 records)
        $penugasanList = [
            [
                'nomor_penugasan' => 'TSK/17000/202610/0001',
                'pemberi' => '19880404004',
                'staf' => '19920606006',
                'kantor' => $kantorPusat->id,
                'jenis' => 'inventarisasi',
                'prioritas' => 'tinggi',
                'tgl_tugas' => '2026-10-10',
                'tenggat' => '2026-10-25',
                'instruksi' => 'Lakukan inventarisasi fisik aset server dan laptop lantai 4 kantor pusat dengan scan QR Code.',
                'status' => 'sedang_dikerjakan',
                'catatan' => 'Sebagian laptop telah dipindai dan dicocokkan kondisinya.',
            ],
            [
                'nomor_penugasan' => 'TSK/17113/202610/0002',
                'pemberi' => '19890505005',
                'staf' => '19930707007',
                'kantor' => $kantorMap['17113']->id,
                'jenis' => 'pemeriksaan_kondisi',
                'prioritas' => 'sedang',
                'tgl_tugas' => '2026-10-12',
                'tenggat' => '2026-10-20',
                'instruksi' => 'Pemeriksaan kondisi switch dan access point di seluruh ruangan kantor cabang Bekasi.',
                'status' => 'menunggu_validasi',
                'catatan' => 'Pemeriksaan selesai, seluruh lampu indikator switch normal.',
            ],
            [
                'nomor_penugasan' => 'TSK/17000/202610/0003',
                'pemberi' => '19880404004',
                'staf' => '19940808008',
                'kantor' => $kantorPusat->id,
                'jenis' => 'pelabelan_qr',
                'prioritas' => 'sedang',
                'tgl_tugas' => '2026-10-05',
                'tenggat' => '2026-10-15',
                'instruksi' => 'Cetak dan tempel label QR Code tahan air untuk 5 unit laptop baru.',
                'status' => 'selesai',
                'catatan' => 'Label telah tertempel rapi di bagian bawah bodi laptop.',
            ],
            [
                'nomor_penugasan' => 'TSK/17113/202610/0004',
                'pemberi' => '19890505005',
                'staf' => '19950909009',
                'kantor' => $kantorMap['17113']->id,
                'jenis' => 'inventarisasi',
                'prioritas' => 'tinggi',
                'tgl_tugas' => '2026-10-08',
                'tenggat' => '2026-10-22',
                'instruksi' => 'Inventarisasi fisik meja dan kursi kantor cabang Bekasi Pasar Lama.',
                'status' => 'sedang_dikerjakan',
                'catatan' => 'Ruang pelayanan customer service selesai dihitung.',
            ],
            [
                'nomor_penugasan' => 'TSK/17000/202610/0005',
                'pemberi' => '19880404004',
                'staf' => '19920606006',
                'kantor' => $kantorPusat->id,
                'jenis' => 'pemeliharaan',
                'prioritas' => 'darurat',
                'tgl_tugas' => '2026-10-17',
                'tenggat' => '2026-10-19',
                'instruksi' => 'Dampingi teknisi Daikin perbaikan AC server standing floor 5 PK.',
                'status' => 'menunggu_validasi',
                'catatan' => 'Kompresor telah normal kembali dan dingin stabil 18C.',
            ],
            [
                'nomor_penugasan' => 'TSK/17113/202610/0006',
                'pemberi' => '19890505005',
                'staf' => '19930707007',
                'kantor' => $kantorMap['17113']->id,
                'jenis' => 'penggantian',
                'prioritas' => 'sedang',
                'tgl_tugas' => '2026-10-14',
                'tenggat' => '2026-10-24',
                'instruksi' => 'Penggantian monitor operasional yang berkedip dengan monitor cadangan.',
                'status' => 'diberikan',
                'catatan' => null,
            ],
            [
                'nomor_penugasan' => 'TSK/17000/202610/0007',
                'pemberi' => '19880404004',
                'staf' => '19940808008',
                'kantor' => $kantorPusat->id,
                'jenis' => 'penarikan',
                'prioritas' => 'rendah',
                'tgl_tugas' => '2026-10-01',
                'tenggat' => '2026-10-07',
                'instruksi' => 'Penarikan laptop lama staf yang mutasi ke cabang Bandung.',
                'status' => 'selesai',
                'catatan' => 'Unit telah disimpan di lemari gudang IT pusat.',
            ],
            [
                'nomor_penugasan' => 'TSK/17113/202610/0008',
                'pemberi' => '19890505005',
                'staf' => '19950909009',
                'kantor' => $kantorMap['17113']->id,
                'jenis' => 'pemindahan',
                'prioritas' => 'sedang',
                'tgl_tugas' => '2026-10-02',
                'tenggat' => '2026-10-05',
                'instruksi' => 'Pemindahan printer multifungsi dari ruang arsip ke ruang manajer.',
                'status' => 'selesai',
                'catatan' => 'Printer terpasang dan terhubung kabel LAN lokal.',
            ],
            [
                'nomor_penugasan' => 'TSK/17000/202610/0009',
                'pemberi' => '19880404004',
                'staf' => '19920606006',
                'kantor' => $kantorPusat->id,
                'jenis' => 'pemeriksaan_kondisi',
                'prioritas' => 'tinggi',
                'tgl_tugas' => '2026-10-18',
                'tenggat' => '2026-10-21',
                'instruksi' => 'Uji coba genset cadangan saat simulasi pemadaman PLN.',
                'status' => 'diberikan',
                'catatan' => null,
            ],
            [
                'nomor_penugasan' => 'TSK/17113/202610/0010',
                'pemberi' => '19890505005',
                'staf' => '19930707007',
                'kantor' => $kantorMap['17113']->id,
                'jenis' => 'inventarisasi',
                'prioritas' => 'sedang',
                'tgl_tugas' => '2026-09-15',
                'tenggat' => '2026-09-30',
                'instruksi' => 'Opname kuartal 3 aset elektronik kantor cabang Bekasi.',
                'status' => 'selesai',
                'catatan' => 'Seluruh aset elektronik tercatat 100% lengkap.',
            ],
        ];

        $penugasanMap = [];
        foreach ($penugasanList as $t) {
            $tgs = PenugasanStaf::create([
                'nomor_penugasan' => $t['nomor_penugasan'],
                'pemberi_tugas_id' => $penggunaMap[$t['pemberi']]->id,
                'staf_pelaksana_id' => $penggunaMap[$t['staf']]->id,
                'kantor_id' => $t['kantor'],
                'jenis_penugasan' => $t['jenis'],
                'prioritas' => $t['prioritas'],
                'tanggal_penugasan' => $t['tgl_tugas'],
                'tenggat_waktu' => $t['tenggat'],
                'instruksi' => $t['instruksi'],
                'status_penugasan' => $t['status'],
                'catatan_pelaksanaan' => $t['catatan'],
                'tanggal_selesai_pelaksanaan' => in_array($t['status'], ['selesai', 'menunggu_validasi']) ? Carbon::parse($t['tenggat'])->subDays(2) : null,
            ]);
            $penugasanMap[$t['nomor_penugasan']] = $tgs;

            // 13. SEED DETAIL PENUGASAN (10 records)
            DetailPenugasan::create([
                'penugasan_staf_id' => $tgs->id,
                'aset_id' => $asetMap['AST/17000/202610/00001']->id,
                'kondisi_sebelum' => 'baik',
                'kondisi_sesudah' => 'sangat_baik',
                'lokasi_ditemukan' => 'Meja Kerja Teknisi Lantai 4',
                'status_pengerjaan' => in_array($t['status'], ['selesai', 'menunggu_validasi']) ? 'selesai' : 'belum',
                'catatan_staf' => 'Pemeriksaan perangkat telah diverifikasi.',
            ]);
        }

        // 14. SEED PENGAJUAN PENGADAAN (10 records)
        $pengadaanList = [
            [
                'nomor' => 'PRC/17000/202610/0001',
                'kantor' => $kantorPusat->id,
                'divisi' => $divisiMap['17002']->id,
                'pengaju' => '19880404004',
                'judul' => 'Pengadaan 5 Unit Laptop High-Performance untuk Tim Pengembang',
                'deskripsi' => 'Penggantian laptop lama yang sudah berumur lebih dari 4 tahun untuk kebutuhan coding dan render arsitektur.',
                'alasan' => 'Meningkatkan produktivitas kerja tim IT engineering.',
                'biaya' => 110000000.00,
                'status' => 'disetujui',
                'tgl_p' => '2026-09-10',
                'tgl_acc' => '2026-09-15',
            ],
            [
                'nomor' => 'PRC/17113/202610/0002',
                'kantor' => $kantorMap['17113']->id,
                'divisi' => $divisiMap['17111']->id,
                'pengaju' => '19890505005',
                'judul' => 'Pengadaan 10 Unit Kursi Ergonomis Ruang Front Office Bekasi',
                'deskripsi' => 'Penggantian kursi lama yang busa dan roda hidroliknya sudah rusak parah.',
                'alasan' => 'Kenyamanan pelayanan nasabah dan staf counter cabang.',
                'biaya' => 18500000.00,
                'status' => 'menunggu_persetujuan_kepala_kantor',
                'tgl_p' => '2026-10-10',
                'tgl_acc' => null,
            ],
            [
                'nomor' => 'PRC/17000/202610/0003',
                'kantor' => $kantorPusat->id,
                'divisi' => $divisiMap['17002']->id,
                'pengaju' => '19880404004',
                'judul' => 'Pengadaan 2 Unit Firewall FortiGate 100F HA Cluster',
                'deskripsi' => 'Penguatan perimeter keamanan siber dan perlindungan intrusi kantor pusat.',
                'alasan' => 'Rekomendasi audit ISO 27001 keamanan informasi.',
                'biaya' => 165000000.00,
                'status' => 'menunggu_persetujuan_kepala_kantor',
                'tgl_p' => '2026-10-12',
                'tgl_acc' => null,
            ],
            [
                'nomor' => 'PRC/17113/202610/0004',
                'kantor' => $kantorMap['17113']->id,
                'divisi' => $divisiMap['17111']->id,
                'pengaju' => '19890505005',
                'judul' => 'Pengadaan 2 Unit AC Inverter 2 PK Ruang Pelayanan',
                'deskripsi' => 'Pendingin udara lama sering mati dan tidak efisien energi.',
                'alasan' => 'Meningkatkan kenyamanan pelanggan di ruang antrean.',
                'biaya' => 19000000.00,
                'status' => 'disetujui',
                'tgl_p' => '2026-09-20',
                'tgl_acc' => '2026-09-23',
            ],
            [
                'nomor' => 'PRC/17000/202610/0005',
                'kantor' => $kantorPusat->id,
                'divisi' => $divisiMap['17005']->id,
                'pengaju' => '19880404004',
                'judul' => 'Pengadaan 3 Unit Barcode Scanner Wireless Zebra DS2278',
                'deskripsi' => 'Alat scanner nirkabel untuk tim inventarisasi aset bergerak.',
                'alasan' => 'Efisiensi opname tahunan seluruh lantai kantor pusat.',
                'biaya' => 14500000.00,
                'status' => 'disetujui',
                'tgl_p' => '2026-08-15',
                'tgl_acc' => '2026-08-18',
            ],
            [
                'nomor' => 'PRC/17113/202610/0006',
                'kantor' => $kantorMap['17113']->id,
                'divisi' => $divisiMap['17111']->id,
                'pengaju' => '19890505005',
                'judul' => 'Pengadaan 1 Unit Mesin Penghancur Kertas Dokumen Rahasia',
                'deskripsi' => 'Shredder cross-cut standar keamanan audit perbankan.',
                'alasan' => 'Pemusnahan berkas kadaluarsa sesuai regulasi perlindungan data pribadi.',
                'biaya' => 7500000.00,
                'status' => 'diajukan',
                'tgl_p' => '2026-10-15',
                'tgl_acc' => null,
            ],
            [
                'nomor' => 'PRC/17000/202610/0007',
                'kantor' => $kantorPusat->id,
                'divisi' => $divisiMap['17004']->id,
                'pengaju' => '19880404004',
                'judul' => 'Pengadaan 1 Unit Kendaraan Operasional Logistik Grand Max Blind Van',
                'deskripsi' => 'Armada angkut barang perlengkapan antar cabang Jabodetabek.',
                'alasan' => 'Menghemat ongkos kirim ekspedisi eksternal yang semakin tinggi.',
                'biaya' => 178000000.00,
                'status' => 'draft',
                'tgl_p' => '2026-10-18',
                'tgl_acc' => null,
            ],
            [
                'nomor' => 'PRC/17113/202610/0008',
                'kantor' => $kantorMap['17113']->id,
                'divisi' => $divisiMap['17112']->id,
                'pengaju' => '19890505005',
                'judul' => 'Pengadaan Brankas Tahan Api Khasanah Cabang Bekasi',
                'deskripsi' => 'Penyimpanan surat berharga dan berkas jaminan pembiayaan.',
                'alasan' => 'Standarisasi pengamanan fisik kantor cabang.',
                'biaya' => 42000000.00,
                'status' => 'disetujui',
                'tgl_p' => '2026-07-10',
                'tgl_acc' => '2026-07-15',
            ],
            [
                'nomor' => 'PRC/17000/202610/0009',
                'kantor' => $kantorPusat->id,
                'divisi' => $divisiMap['17002']->id,
                'pengaju' => '19880404004',
                'judul' => 'Pengadaan 4 Unit Smart TV 65 Inch Ruang Rapat Direksi',
                'deskripsi' => 'Display video conference 4K dengan kamera terintegrasi.',
                'alasan' => 'Mendukung rapat hybrid meeting antar cabang.',
                'biaya' => 58000000.00,
                'status' => 'selesai',
                'tgl_p' => '2026-06-01',
                'tgl_acc' => '2026-06-05',
            ],
            [
                'nomor' => 'PRC/17113/202610/0010',
                'kantor' => $kantorMap['17113']->id,
                'divisi' => $divisiMap['17111']->id,
                'pengaju' => '19890505005',
                'judul' => 'Pengadaan Perangkat UPS Online 6000VA Cabang Bekasi',
                'deskripsi' => 'Proteksi daya listrik ruang server cabang saat genset switching.',
                'alasan' => 'Mencegah server mati mendadak saat listrik PLN padam.',
                'biaya' => 32000000.00,
                'status' => 'perlu_revisi',
                'tgl_p' => '2026-10-05',
                'tgl_acc' => null,
            ],
        ];

        $pengadaanMap = [];
        foreach ($pengadaanList as $prc) {
            $pModel = PengajuanPengadaan::create([
                'nomor_pengadaan' => $prc['nomor'],
                'kantor_id' => $prc['kantor'],
                'divisi_id' => $prc['divisi'],
                'pengaju_id' => $penggunaMap[$prc['pengaju']]->id,
                'judul_pengadaan' => $prc['judul'],
                'deskripsi_kebutuhan' => $prc['deskripsi'],
                'alasan_pengadaan' => $prc['alasan'],
                'estimasi_total_biaya' => $prc['biaya'],
                'status_pengadaan' => $prc['status'],
                'tanggal_pengajuan' => $prc['tgl_p'],
                'tanggal_disetujui' => $prc['tgl_acc'],
            ]);
            $pengadaanMap[$prc['nomor']] = $pModel;

            // 15. SEED DETAIL PENGAJUAN PENGADAAN (10 records)
            DetailPengajuanPengadaan::create([
                'pengajuan_pengadaan_id' => $pModel->id,
                'kategori_aset_id' => $kategoriMap['KAT-01']->id,
                'nama_barang' => $prc['judul'],
                'spesifikasi' => 'Standar enterprise spesifikasi pabrikan resmi bergaransi 3 tahun.',
                'jumlah' => 1,
                'estimasi_harga_satuan' => $prc['biaya'],
                'estimasi_subtotal' => $prc['biaya'],
            ]);

            // 16. SEED PERSETUJUAN & RIWAYAT (10 records)
            $persetujuan = Persetujuan::create([
                'nomor_dokumen_referensi' => $prc['nomor'],
                'tipe_dokumen' => 'pengadaan',
                'dokumen_id' => $pModel->id,
                'pemohon_id' => $penggunaMap[$prc['pengaju']]->id,
                'penyetuju_id' => $penggunaMap['19860202002']->id, // Kepala Kantor
                'level_persetujuan' => 2,
                'status_keputusan' => $prc['status'] === 'disetujui' ? 'disetujui' : ($prc['status'] === 'perlu_revisi' ? 'dikembalikan' : 'menunggu'),
                'komentar' => $prc['status'] === 'disetujui' ? 'Disetujui untuk diproses ke Bagian Pengadaan.' : 'Menunggu telaah anggaran.',
                'tanggal_keputusan' => $prc['tgl_acc'] ? Carbon::parse($prc['tgl_acc'])->toDateTimeString() : null,
            ]);

            RiwayatPersetujuan::create([
                'persetujuan_id' => $persetujuan->id,
                'penyetuju_id' => $penggunaMap['19860202002']->id,
                'aksi' => $prc['status'] === 'disetujui' ? 'disetujui' : 'diajukan',
                'catatan' => 'Verifikasi pengajuan pengadaan oleh Kepala Kantor.',
                'waktu_aksi' => now(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        // 17. SEED BAST (Berita Acara Serah Terima - 10 records)
        $bastList = [
            ['nomor' => 'BAST/17000/202610/0001', 'kantor' => $kantorPusat->id, 'tgl' => '2026-01-15', 'jenis' => 'pengadaan_baru', 'penyerah' => '19880404004', 'penerima' => '19920606006', 'manajer' => '19880404004', 'status' => 'selesai_disahkan', 'catatan' => 'Serah terima laptop ThinkPad T14 baru.'],
            ['nomor' => 'BAST/17113/202610/0002', 'kantor' => $kantorMap['17113']->id, 'tgl' => '2026-03-10', 'jenis' => 'penyerahan_karyawan', 'penyerah' => '19890505005', 'penerima' => '19930707007', 'manajer' => '19890505005', 'status' => 'selesai_disahkan', 'catatan' => 'Serah terima laptop Dell Latitude inventaris kerja staf.'],
            ['nomor' => 'BAST/17000/202610/0003', 'kantor' => $kantorPusat->id, 'tgl' => '2026-01-20', 'jenis' => 'penyerahan_karyawan', 'penyerah' => '19880404004', 'penerima' => '19860202002', 'manajer' => '19880404004', 'status' => 'selesai_disahkan', 'catatan' => 'Serah terima mobil dinas Toyota Innova Zenix Kepala Kantor Pusat.'],
            ['nomor' => 'BAST/17113/202610/0004', 'kantor' => $kantorMap['17113']->id, 'tgl' => '2026-03-12', 'jenis' => 'penyerahan_karyawan', 'penyerah' => '19890505005', 'penerima' => '19870303003', 'manajer' => '19890505005', 'status' => 'selesai_disahkan', 'catatan' => 'Serah terima mobil operasional Toyota Avanza Kepala Cabang Bekasi.'],
            ['nomor' => 'BAST/17000/202610/0005', 'kantor' => $kantorPusat->id, 'tgl' => '2026-01-05', 'jenis' => 'pengadaan_baru', 'penyerah' => '19880404004', 'penerima' => '19880404004', 'manajer' => '19880404004', 'status' => 'selesai_disahkan', 'catatan' => 'Penerimaan sewa mesin fotokopi Fuji Xerox C3570 dari vendor.'],
            ['nomor' => 'BAST/17113/202610/0006', 'kantor' => $kantorMap['17113']->id, 'tgl' => '2026-02-05', 'jenis' => 'pengadaan_baru', 'penyerah' => '19890505005', 'penerima' => '19890505005', 'manajer' => '19890505005', 'status' => 'selesai_disahkan', 'catatan' => 'Penerimaan sewa mesin fotokopi Fuji Xerox C3070 Bekasi.'],
            ['nomor' => 'BAST/17000/202610/0007', 'kantor' => $kantorPusat->id, 'tgl' => '2026-10-10', 'jenis' => 'pemindahan', 'penyerah' => '19880404004', 'penerima' => '19890505005', 'manajer' => '19880404004', 'status' => 'menunggu_persetujuan_manajer', 'catatan' => 'Draft mutasi 2 unit switch dari pusat ke cabang Bekasi.'],
            ['nomor' => 'BAST/17113/202610/0008', 'kantor' => $kantorMap['17113']->id, 'tgl' => '2026-10-12', 'jenis' => 'pengembalian', 'penyerah' => '19930707007', 'penerima' => '19890505005', 'manajer' => '19890505005', 'status' => 'menunggu_tanda_tangan', 'catatan' => 'Pengembalian laptop perbaikan ke gudang logistik cabang.'],
            ['nomor' => 'BAST/17000/202610/0009', 'kantor' => $kantorPusat->id, 'tgl' => '2026-10-15', 'jenis' => 'penggantian', 'penyerah' => '19920606006', 'penerima' => '19880404004', 'manajer' => '19880404004', 'status' => 'draft', 'catatan' => 'Draft penggantian monitor display ruang rapat direksi.'],
            ['nomor' => 'BAST/17113/202610/0010', 'kantor' => $kantorMap['17113']->id, 'tgl' => '2026-09-01', 'jenis' => 'pengadaan_baru', 'penyerah' => '19890505005', 'penerima' => '19950909009', 'manajer' => '19890505005', 'status' => 'selesai_disahkan', 'catatan' => 'Serah terima brankas tahan api khasanah kantor cabang Bekasi.'],
        ];

        foreach ($bastList as $b) {
            $bastModel = BeritaAcaraSerahTerima::create([
                'nomor_bast' => $b['nomor'],
                'tanggal_bast' => $b['tgl'],
                'jenis_transaksi' => $b['jenis'],
                'kantor_id' => $b['kantor'],
                'divisi_id' => null,
                'pihak_penyerah_id' => $penggunaMap[$b['penyerah']]->id,
                'pihak_penerima_id' => $penggunaMap[$b['penerima']]->id,
                'disetujui_manajer_id' => $penggunaMap[$b['manajer']]->id,
                'status_bast' => $b['status'],
                'catatan' => $b['catatan'],
            ]);

            // 18. SEED DETAIL BAST (10 records)
            DetailBast::create([
                'berita_acara_serah_terima_id' => $bastModel->id,
                'aset_id' => $asetMap['AST/17000/202610/00001']->id,
                'kondisi_saat_serah' => 'sangat_baik',
                'keterangan' => 'Unit lengkap bersama adaptor charger original dan tas laptop.',
            ]);
        }

        // 19. SEED PENYUSUTAN ASET (10 records - Historis Akuntansi)
        $aset1 = $asetMap['AST/17000/202610/00001'];
        for ($bln = 1; $bln <= 10; $bln++) {
            $akumulasi = 412500.00 * $bln;
            PenyusutanAset::create([
                'aset_id' => $aset1->id,
                'periode_tahun' => 2026,
                'periode_bulan' => $bln,
                'nilai_perolehan' => $aset1->nilai_perolehan,
                'beban_penyusutan' => 412500.00,
                'akumulasi_penyusutan' => 3712500.00 + $akumulasi,
                'nilai_buku' => $aset1->nilai_perolehan - (3712500.00 + $akumulasi),
                'metode_penyusutan' => 'garis_lurus',
                'dihitung_otomatis' => true,
                'tanggal_perhitungan' => Carbon::create(2026, $bln, 1)->endOfMonth()->toDateString(),
            ]);
        }

        // 20. SEED REKOMENDASI PEREMAJAAN (10 records)
        $rekomendasiList = [
            ['aset' => 'AST/17000/202610/00007', 'skor' => 85, 'rusak_freq' => 6, 'biaya' => 8500000.00, 'usia' => 80.00, 'prioritas' => 'segera', 'alasan' => 'Biaya perbaikan AC standing sudah melebihi 40% nilai buku dan kompresor aus berulang.', 'estimasi' => 42000000.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17113/202610/00002', 'skor' => 40, 'rusak_freq' => 2, 'biaya' => 800000.00, 'usia' => 35.00, 'prioritas' => 'rendah', 'alasan' => 'Laptop masih dalam performa baik, hanya perlu upgrade RAM 32GB jika diperlukan.', 'estimasi' => 1500000.00, 'status' => 'ditunda'],
            ['aset' => 'AST/17000/202610/00001', 'skor' => 30, 'rusak_freq' => 1, 'biaya' => 250000.00, 'usia' => 38.00, 'prioritas' => 'rendah', 'alasan' => 'Kondisi ThinkPad sangat prima, masa pakai tersisa 28 bulan.', 'estimasi' => 0.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17113/202610/00004', 'skor' => 45, 'rusak_freq' => 2, 'biaya' => 950000.00, 'usia' => 42.00, 'prioritas' => 'sedang', 'alasan' => 'Switch Cisco beroperasi stabil, dianjurkan pembaruan lisensi DNA.', 'estimasi' => 12000000.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17000/202610/00005', 'skor' => 35, 'rusak_freq' => 1, 'biaya' => 2650000.00, 'usia' => 30.00, 'prioritas' => 'rendah', 'alasan' => 'Mobil dinas rutin servis berkala, performa mesin 100% prima.', 'estimasi' => 0.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17113/202610/00006', 'skor' => 50, 'rusak_freq' => 3, 'biaya' => 4200000.00, 'usia' => 32.00, 'prioritas' => 'sedang', 'alasan' => 'Penggantian ban depan dan servis rem berkala disarankan.', 'estimasi' => 3500000.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17000/202610/00003', 'skor' => 25, 'rusak_freq' => 0, 'biaya' => 1200000.00, 'usia' => 40.00, 'prioritas' => 'rendah', 'alasan' => 'Server data center beroperasi dalam kondisi lingkungan sejuk terkontrol.', 'estimasi' => 0.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17000/202610/00008', 'skor' => 60, 'rusak_freq' => 4, 'biaya' => 0.00, 'usia' => 50.00, 'prioritas' => 'sedang', 'alasan' => 'Mesin fotokopi sewa mendekati evaluasi volume cetak tahunan dengan vendor.', 'estimasi' => 0.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17113/202610/00009', 'skor' => 40, 'rusak_freq' => 2, 'biaya' => 0.00, 'usia' => 45.00, 'prioritas' => 'rendah', 'alasan' => 'Mesin sewa cabang Bekasi beroperasi baik tanpa kendala serius.', 'estimasi' => 0.00, 'status' => 'menunggu'],
            ['aset' => 'AST/17000/202610/00010', 'skor' => 20, 'rusak_freq' => 0, 'biaya' => 0.00, 'usia' => 55.00, 'prioritas' => 'rendah', 'alasan' => 'Gateway Cisco Telkom SLA tinggi dan performa jaringan leased line optimal.', 'estimasi' => 0.00, 'status' => 'menunggu'],
        ];

        foreach ($rekomendasiList as $rek) {
            RekomendasiPeremajaan::create([
                'aset_id' => $asetMap[$rek['aset']]->id,
                'skor_kondisi' => $rek['skor'],
                'frekuensi_kerusakan' => $rek['rusak_freq'],
                'total_biaya_pemeliharaan' => $rek['biaya'],
                'persentase_usia_terpakai' => $rek['usia'],
                'prioritas_rekomendasi' => $rek['prioritas'],
                'alasan_rekomendasi' => $rek['alasan'],
                'estimasi_biaya_penggantian' => $rek['estimasi'],
                'status_evaluasi' => $rek['status'],
            ]);
        }

        // 21. SEED NOTIFIKASI (10 records)
        $notifikasiList = [
            ['user' => '19920606006', 'judul' => 'Tugas Baru Diterima', 'pesan' => 'Anda ditugaskan melakukan inventarisasi aset IT server dan laptop pusat.', 'tipe' => 'tugas_baru'],
            ['user' => '19880404004', 'judul' => 'Hasil Kerja Menunggu Validasi', 'pesan' => 'Staf Budi Santoso telah menyelesaikan perbaikan AC server standing floor.', 'tipe' => 'validasi_pekerjaan'],
            ['user' => '19860202002', 'judul' => 'Persetujuan Pengadaan Menunggu', 'pesan' => 'Pengajuan pengadaan Firewall FortiGate Cluster menunggu persetujuan Anda.', 'tipe' => 'persetujuan_menunggu'],
            ['user' => '19930707007', 'judul' => 'Tugas Baru Cabang Bekasi', 'pesan' => 'Pemeriksaan kondisi switch dan access point cabang Bekasi Pasar Lama.', 'tipe' => 'tugas_baru'],
            ['user' => '19890505005', 'judul' => 'Validasi Tugas Staf Bekasi', 'pesan' => 'Staf Andi Pratama mengirimkan hasil pemeriksaan perangkat jaringan.', 'tipe' => 'validasi_pekerjaan'],
            ['user' => '19870303003', 'judul' => 'Pengadaan Kursi Kantor Bekasi', 'pesan' => 'Pengajuan kursi front office menunggu persetujuan Kepala Cabang.', 'tipe' => 'persetujuan_menunggu'],
            ['user' => '19880404004', 'judul' => 'Jadwal Pemeliharaan Mendekati Tenggat', 'pesan' => 'Jadwal pemeliharaan rutin ThinkPad T14 jatuh tempo dalam 3 hari.', 'tipe' => 'pengingat_pemeliharaan'],
            ['user' => '19880404004', 'judul' => 'Aset Memerlukan Peremajaan', 'pesan' => 'AC Standing Daikin Ruang Server direkomendasikan untuk penggantian segera.', 'tipe' => 'rekomendasi_peremajaan'],
            ['user' => '19890505005', 'judul' => 'BAST Menunggu Pengesahan', 'pesan' => 'Dokumen BAST penyerahan brankas cabang Bekasi siap ditandatangani.', 'tipe' => 'bast_siap'],
            ['user' => '19850101001', 'judul' => 'Sistem SI-ASET Siap Digunakan', 'pesan' => 'Inisialisasi database dan sinkronisasi hak akses pengguna berhasil.', 'tipe' => 'sistem'],
        ];

        foreach ($notifikasiList as $notif) {
            Notifikasi::create([
                'pengguna_id' => $penggunaMap[$notif['user']]->id,
                'judul' => $notif['judul'],
                'pesan' => $notif['pesan'],
                'tipe_notifikasi' => $notif['tipe'],
                'referensi_tipe' => null,
                'referensi_id' => null,
                'sudah_dibaca' => false,
            ]);
        }

        // 22. SEED LOG AKTIVITAS (10 records)
        $logList = [
            ['user' => '19850101001', 'kantor' => $kantorPusat->id, 'modul' => 'Autentikasi', 'aksi' => 'login', 'desc' => 'Admin Rahmat Hidayat berhasil login ke dashboard SI-ASET.'],
            ['user' => '19880404004', 'kantor' => $kantorPusat->id, 'modul' => 'Aset', 'aksi' => 'create', 'desc' => 'Manajer meregistrasikan aset ThinkPad T14 Gen 4.'],
            ['user' => '19890505005', 'kantor' => $kantorMap['17113']->id, 'modul' => 'Aset', 'aksi' => 'create', 'desc' => 'Manajer Bekasi meregistrasikan aset Dell Latitude 5440.'],
            ['user' => '19880404004', 'kantor' => $kantorPusat->id, 'modul' => 'Penugasan', 'aksi' => 'create', 'desc' => 'Manajer membuat tugas inventarisasi TSK/17000/202610/0001 kepada Staf Budi.'],
            ['user' => '19920606006', 'kantor' => $kantorPusat->id, 'modul' => 'Penugasan', 'aksi' => 'update', 'desc' => 'Staf Budi memperbarui status penugasan dan mencocokkan kondisi aset.'],
            ['user' => '19880404004', 'kantor' => $kantorPusat->id, 'modul' => 'Pengadaan', 'aksi' => 'create', 'desc' => 'Pengajuan proposal pengadaan laptop high-performance diajukan.'],
            ['user' => '19860202002', 'kantor' => $kantorPusat->id, 'modul' => 'Persetujuan', 'aksi' => 'approve', 'desc' => 'Kepala Kantor menyetujui pengadaan laptop high-performance.'],
            ['user' => '19880404004', 'kantor' => $kantorPusat->id, 'modul' => 'BAST', 'aksi' => 'create', 'desc' => 'Penerbitan Berita Acara Serah Terima BAST/17000/202610/0001.'],
            ['user' => '19920606006', 'kantor' => $kantorPusat->id, 'modul' => 'Pemeliharaan', 'aksi' => 'create', 'desc' => 'Pencatatan tindakan pemeliharaan servis AC Daikin ruang server.'],
            ['user' => '19850101001', 'kantor' => $kantorPusat->id, 'modul' => 'Depresiasi', 'aksi' => 'calculate', 'desc' => 'Perhitungan batch depresiasi bulanan garis lurus periode Oktober 2026.'],
        ];

        foreach ($logList as $lg) {
            LogAktivitas::create([
                'pengguna_id' => $penggunaMap[$lg['user']]->id,
                'kantor_id' => $lg['kantor'],
                'modul' => $lg['modul'],
                'aksi' => $lg['aksi'],
                'deskripsi' => $lg['desc'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SI-ASET Android Client / OkHttp 4.9.3',
            ]);
        }

        // 23. SEED PENGATURAN SISTEM (10 records)
        $pengaturanList = [
            ['kunci' => 'nama_perusahaan', 'nilai' => 'PT ASET NUSANTARA SEJAHTERA', 'kategori' => 'umum', 'deskripsi' => 'Nama resmi korporasi pada kop surat laporan PDF.'],
            ['kunci' => 'alamat_perusahaan', 'nilai' => 'Gedung Menara Mandiri Lt. 28, Jl. Jend. Sudirman Kav. 54-55, Jakarta Selatan 12190', 'kategori' => 'umum', 'deskripsi' => 'Alamat kantor pusat pada dokumen resmi.'],
            ['kunci' => 'telepon_perusahaan', 'nilai' => '(021) 5299-8800', 'kategori' => 'umum', 'deskripsi' => 'Nomor telepon resmi perusahaan.'],
            ['kunci' => 'email_perusahaan', 'nilai' => 'info@asetnusantara.co.id', 'kategori' => 'umum', 'deskripsi' => 'Alamat email korespondensi resmi.'],
            ['kunci' => 'format_nomor_index_aset', 'nilai' => 'AST/{KODE_KANTOR}/{TAHUN}{BULAN}/{SEQUENCE:05d}', 'kategori' => 'penomoran', 'deskripsi' => 'Format standar pembentukan nomor index unik aset.'],
            ['kunci' => 'metode_depresiasi_default', 'nilai' => 'garis_lurus', 'kategori' => 'akuntansi', 'deskripsi' => 'Metode perhitungan penyusutan aset bawaan perusahaan.'],
            ['kunci' => 'batas_nominal_approval_kepala_kantor', 'nilai' => '10000000', 'kategori' => 'approval', 'deskripsi' => 'Batas nominal pengadaan (Rp 10.000.000) yang mewajibkan persetujuan Kepala Kantor.'],
            ['kunci' => 'hari_pengingat_jadwal_servis', 'nilai' => '7', 'kategori' => 'notifikasi', 'deskripsi' => 'Pengingat otomatis H-7 sebelum jatuh tempo jadwal servis berkala.'],
            ['kunci' => 'hari_pengingat_kontrak_sewa', 'nilai' => '30', 'kategori' => 'notifikasi', 'deskripsi' => 'Pengingat otomatis H-30 sebelum masa kontrak sewa berakhir.'],
            ['kunci' => 'versi_aplikasi_android_min', 'nilai' => '1.0.0', 'kategori' => 'sistem', 'deskripsi' => 'Versi minimal aplikasi Android yang diizinkan terhubung ke API.'],
        ];

        foreach ($pengaturanList as $st) {
            PengaturanSistem::create([
                'kunci_pengaturan' => $st['kunci'],
                'nilai_pengaturan' => $st['nilai'],
                'kategori' => $st['kategori'],
                'deskripsi' => $st['deskripsi'],
            ]);
        }
    }
}
