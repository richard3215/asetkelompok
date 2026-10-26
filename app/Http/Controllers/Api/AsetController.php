<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Kantor;
use App\Models\KategoriAset;
use App\Models\RiwayatAset;
use App\Services\AuditLogService;
use App\Services\NomorIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorPNG;

class AsetController extends Controller
{
    /**
     * Tampilkan daftar aset dengan filter, pencarian, dan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $isPusat = $pengguna->kantor && $pengguna->kantor->jenis_kantor === 'pusat';

        $query = Aset::with(['kategori', 'kantor', 'divisi', 'penanggungJawab', 'vendor', 'kontrakSewa']);

        // Pembatasan data berdasarkan kantor (Kecuali Admin atau Unit Kantor Pusat)
        if (!$pengguna->memilikiPeran('admin') && !$isPusat) {
            $query->where('kantor_id', $pengguna->kantor_id);
        } elseif ($request->filled('kantor_id')) {
            $query->where('kantor_id', $request->kantor_id);
        }

        // Filter Pencarian Teks
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_aset', 'like', "%{$q}%")
                    ->orWhere('nomor_index', 'like', "%{$q}%")
                    ->orWhere('nomor_seri', 'like', "%{$q}%")
                    ->orWhere('barcode_vendor', 'like', "%{$q}%")
                    ->orWhere('merk', 'like', "%{$q}%");
            });
        }

        // Filter Spesifik
        if ($request->filled('kategori_id')) {
            $query->where('kategori_aset_id', $request->kategori_id);
        }
        if ($request->filled('jenis_kepemilikan')) {
            $query->where('jenis_kepemilikan', $request->jenis_kepemilikan);
        }
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }
        if ($request->filled('status_operasional')) {
            $query->where('status_operasional', $request->status_operasional);
        }
        if ($request->filled('status_inventaris')) {
            $query->where('status_inventaris', $request->status_inventaris);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $daftarAset = $query->latest('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'pesan' => 'Daftar aset berhasil diambil.',
            'data' => $daftarAset,
        ]);
    }

    /**
     * Tampilkan detail lengkap aset beserta seluruh riwayat dan jadwal pemeliharaannya.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $pengguna = $request->user();
        $isPusat = $pengguna->kantor && $pengguna->kantor->jenis_kantor === 'pusat';

        $aset = Aset::with([
            'kategori',
            'kantor',
            'divisi',
            'penanggungJawab',
            'vendor',
            'kontrakSewa',
            'riwayat.pengguna',
            'jadwalPemeliharaan',
            'pemeliharaan.teknisi',
            'penyusutan',
            'rekomendasiPeremajaan',
        ])->find($id);

        if (!$aset) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Aset tidak ditemukan.',
            ], 404);
        }

        // Validasi pembatasan kantor
        if (!$pengguna->memilikiPeran('admin') && !$isPusat && $aset->kantor_id !== $pengguna->kantor_id) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Akses ditolak. Aset ini berada di luar unit kerja wewenang Anda.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'pesan' => 'Detail aset berhasil diambil.',
            'data' => $aset,
        ]);
    }

    /**
     * Registrasi aset baru dengan nomor index otomatis dan pencegahan duplikasi.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_aset' => 'required|string|max:200',
            'kategori_aset_id' => 'required|exists:kategori_aset,id',
            'jenis_kepemilikan' => 'required|in:milik_sendiri,sewa_vendor',
            'kantor_id' => 'required|exists:kantor,id',
            'divisi_id' => 'nullable|exists:divisi,id',
            'penanggung_jawab_id' => 'nullable|exists:pengguna,id',
            'kontrak_sewa_id' => 'nullable|required_if:jenis_kepemilikan,sewa_vendor|exists:kontrak_sewa,id',
            'vendor_id' => 'nullable|exists:vendor,id',
            'merk' => 'nullable|string|max:100',
            'tipe_model' => 'nullable|string|max:100',
            'nomor_seri' => 'nullable|string|max:100|unique:aset,nomor_seri',
            'barcode_vendor' => 'nullable|string|max:100',
            'kondisi' => 'required|in:sangat_baik,baik,rusak_ringan,rusak_berat,tidak_ekonomis',
            'tanggal_perolehan' => 'required|date',
            'sumber_pengadaan' => 'nullable|string|max:150',
            'nilai_perolehan' => 'nullable|numeric|min:0',
            'nilai_residu' => 'nullable|numeric|min:0',
            'masa_manfaat_bulan' => 'nullable|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'spesifikasi_teknis' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi data aset baru gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kantor = Kantor::findOrFail($request->kantor_id);
        $kategori = KategoriAset::findOrFail($request->kategori_aset_id);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('aset', 'public');
        }

        $aset = DB::transaction(function () use ($request, $kantor, $kategori, $fotoPath) {
            $nomorIndex = NomorIndexService::buatNomorIndexAset($kantor->kode_kantor);

            $nilaiPerolehan = $request->jenis_kepemilikan === 'milik_sendiri' ? (float) $request->get('nilai_perolehan', 0) : 0;
            $nilaiResidu = $request->jenis_kepemilikan === 'milik_sendiri' ? (float) $request->get('nilai_residu', 0) : 0;
            $masaManfaat = (int) $request->get('masa_manfaat_bulan', $kategori->masa_manfaat_bulan);
            $metode = $request->jenis_kepemilikan === 'milik_sendiri' ? $kategori->metode_penyusutan_default : 'tanpa_penyusutan';

            $asetBaru = Aset::create([
                'nomor_index' => $nomorIndex,
                'nomor_seri' => $request->nomor_seri,
                'barcode_vendor' => $request->barcode_vendor,
                'nama_aset' => $request->nama_aset,
                'kategori_aset_id' => $kategori->id,
                'jenis_kepemilikan' => $request->jenis_kepemilikan,
                'kontrak_sewa_id' => $request->kontrak_sewa_id,
                'vendor_id' => $request->vendor_id,
                'merk' => $request->merk,
                'tipe_model' => $request->tipe_model,
                'kondisi' => $request->kondisi,
                'status_operasional' => 'terdaftar',
                'kantor_id' => $kantor->id,
                'divisi_id' => $request->divisi_id,
                'penanggung_jawab_id' => $request->penanggung_jawab_id,
                'tanggal_perolehan' => $request->tanggal_perolehan,
                'sumber_pengadaan' => $request->sumber_pengadaan,
                'nilai_perolehan' => $nilaiPerolehan,
                'nilai_residu' => $nilaiResidu,
                'masa_manfaat_bulan' => $masaManfaat,
                'metode_penyusutan' => $metode,
                'tanggal_mulai_penyusutan' => $request->jenis_kepemilikan === 'milik_sendiri' ? $request->tanggal_perolehan : null,
                'nilai_buku_saat_ini' => $nilaiPerolehan,
                'akumulasi_penyusutan' => 0,
                'foto_aset' => $fotoPath,
                'spesifikasi_teknis' => $request->spesifikasi_teknis,
                'status_inventaris' => 'belum_diperiksa',
            ]);

            AuditLogService::catatMutasiAset(
                $asetBaru->id,
                'registrasi',
                "Pendaftaran aset baru [{$asetBaru->nomor_index}] {$asetBaru->nama_aset}",
                null,
                $kantor->id,
                null,
                $request->divisi_id,
                null,
                $asetBaru->kondisi,
                null,
                $asetBaru->status_operasional
            );

            return $asetBaru;
        });

        return response()->json([
            'status' => 'success',
            'pesan' => 'Aset berhasil didaftarkan.',
            'data' => $aset->load(['kategori', 'kantor', 'divisi']),
        ], 201);
    }

    /**
     * Perbarui data aset dengan jejak audit menyeluruh.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $aset = Aset::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_aset' => 'sometimes|required|string|max:200',
            'kondisi' => 'sometimes|required|in:sangat_baik,baik,rusak_ringan,rusak_berat,tidak_ekonomis',
            'status_operasional' => 'sometimes|required|in:draft,diterima,terdaftar,aktif,dalam_pemeliharaan,menunggu_persetujuan,diganti,dipindahkan,dikembalikan,dihapuskan',
            'kantor_id' => 'sometimes|required|exists:kantor,id',
            'divisi_id' => 'nullable|exists:divisi,id',
            'penanggung_jawab_id' => 'nullable|exists:pengguna,id',
            'merk' => 'nullable|string|max:100',
            'tipe_model' => 'nullable|string|max:100',
            'spesifikasi_teknis' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi perbaruan aset gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataLama = $aset->only(['kondisi', 'status_operasional', 'kantor_id', 'divisi_id', 'penanggung_jawab_id']);

        if ($request->hasFile('foto')) {
            $aset->foto_aset = $request->file('foto')->store('aset', 'public');
        }

        $aset->fill($request->except('foto'));
        $aset->save();

        AuditLogService::catatMutasiAset(
            $aset->id,
            'perubahan_data',
            "Pembaruan data aset {$aset->nomor_index}",
            $dataLama['kantor_id'],
            $aset->kantor_id,
            $dataLama['divisi_id'],
            $aset->divisi_id,
            $dataLama['kondisi'],
            $aset->kondisi,
            $dataLama['status_operasional'],
            $aset->status_operasional,
            $dataLama,
            $aset->only(['kondisi', 'status_operasional', 'kantor_id', 'divisi_id', 'penanggung_jawab_id'])
        );

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data aset berhasil diperbarui.',
            'data' => $aset->load(['kategori', 'kantor', 'divisi', 'penanggungJawab']),
        ]);
    }

    /**
     * Pindai (Scan) QR Code atau Barcode Code 128 untuk membuka detail aset.
     */
    public function scan(Request $request, string $kode): JsonResponse
    {
        $pengguna = $request->user();
        $isPusat = $pengguna->kantor && $pengguna->kantor->jenis_kantor === 'pusat';

        $kodeClean = trim($kode);

        $aset = Aset::with(['kategori', 'kantor', 'divisi', 'penanggungJawab', 'vendor', 'kontrakSewa'])
            ->where('nomor_index', $kodeClean)
            ->orWhere('barcode_vendor', $kodeClean)
            ->orWhere('nomor_seri', $kodeClean)
            ->first();

        if (!$aset) {
            return response()->json([
                'status' => 'error',
                'pesan' => "Aset dengan kode [{$kodeClean}] tidak ditemukan di sistem SI-ASET.",
            ], 404);
        }

        $memilikiAkses = $pengguna->memilikiPeran('admin') || $isPusat || ($aset->kantor_id === $pengguna->kantor_id);

        AuditLogService::catat('Aset', 'scan', "Pemindaian aset [{$aset->nomor_index}] via scanner/kamera Android.");

        return response()->json([
            'status' => 'success',
            'pesan' => 'Aset berhasil diidentifikasi.',
            'memiliki_hak_akses' => $memilikiAkses,
            'data' => $aset,
        ]);
    }

    /**
     * Hasilkan gambar Barcode format Code 128 (Base64 PNG).
     */
    public function barcode($id): JsonResponse
    {
        $aset = Aset::findOrFail($id);
        $generator = new BarcodeGeneratorPNG();
        
        // Gunakan nomor index atau barcode vendor
        $kodeBarcode = $aset->barcode_vendor ?: $aset->nomor_index;
        $barcodeData = $generator->getBarcode($kodeBarcode, $generator::TYPE_CODE_128, 2, 60);

        return response()->json([
            'status' => 'success',
            'nomor_index' => $aset->nomor_index,
            'kode' => $kodeBarcode,
            'barcode_base64' => 'data:image/png;base64,' . base64_encode($barcodeData),
        ]);
    }

    /**
     * Update hasil pemeriksaan inventarisasi fisik opname aset.
     */
    public function inventarisasi(Request $request, $id): JsonResponse
    {
        $aset = Aset::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status_inventaris' => 'required|in:ditemukan,lokasi_berbeda,rusak,hilang',
            'kondisi' => 'required|in:sangat_baik,baik,rusak_ringan,rusak_berat,tidak_ekonomis',
            'lokasi_ditemukan' => 'nullable|string|max:150',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Validasi inventarisasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kondisiSebelum = $aset->kondisi;
        $aset->status_inventaris = $request->status_inventaris;
        $aset->kondisi = $request->kondisi;
        $aset->save();

        AuditLogService::catatMutasiAset(
            $aset->id,
            'inventarisasi',
            "Opname fisik aset [{$aset->nomor_index}]: {$request->status_inventaris}. Catatan: {$request->catatan}",
            $aset->kantor_id,
            $aset->kantor_id,
            $aset->divisi_id,
            $aset->divisi_id,
            $kondisiSebelum,
            $aset->kondisi,
            $aset->status_operasional,
            $aset->status_operasional
        );

        return response()->json([
            'status' => 'success',
            'pesan' => 'Inventarisasi aset berhasil dicatat.',
            'data' => $aset,
        ]);
    }
}
