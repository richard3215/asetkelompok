import 'dart:io';
import 'package:flutter/foundation.dart';

class ApiEndpoints {
  // Default host determination based on platform
  static String get defaultBaseUrl {
    if (kIsWeb) {
      return 'http://192.168.1.5:8000/api/v1';
    } else if (Platform.isAndroid) {
      // 10.0.2.2 is standard Android emulator loopback to host PC
      return 'http://10.0.2.2:8000/api/v1';
    } else {
      return 'http://127.0.0.1:8000/api/v1';
    }
  }

  // Active base URL (can be overridden by user in settings)
  static String baseUrl = defaultBaseUrl;

  static void setBaseUrl(String url) {
    baseUrl = url;
  }

  // Auth
  static String get login => '$baseUrl/auth/login';
  static String get profil => '$baseUrl/auth/profil';
  static String get logout => '$baseUrl/auth/logout';

  // Dashboard
  static String get dashboard => '$baseUrl/dashboard';

  // Aset
  static String get aset => '$baseUrl/aset';
  static String get scanAset => '$baseUrl/aset/scan';
  static String scanAsetByKode(String kode) => '$baseUrl/aset/scan/$kode';
  static String asetDetail(int id) => '$baseUrl/aset/$id';
  static String asetBarcode(int id) => '$baseUrl/aset/$id/barcode';
  static String asetInventarisasi(int id) => '$baseUrl/aset/$id/inventarisasi';

  // Penugasan Staf
  static String get penugasan => '$baseUrl/penugasan';
  static String penugasanDetail(int id) => '$baseUrl/penugasan/$id';
  static String penugasanKerjakan(int id) => '$baseUrl/penugasan/$id/kerjakan';
  static String penugasanKirimValidasi(int id) => '$baseUrl/penugasan/$id/kirim-validasi';
  static String penugasanValidasi(int id) => '$baseUrl/penugasan/$id/validasi';

  // Pengadaan & Persetujuan
  static String get pengadaan => '$baseUrl/pengadaan';
  static String pengadaanPutusan(int id) => '$baseUrl/pengadaan/$id/putusan';

  // Pemeliharaan
  static String get pemeliharaanJadwal => '$baseUrl/pemeliharaan/jadwal';
  static String get pemeliharaanRiwayat => '$baseUrl/pemeliharaan/riwayat';
  static String get pemeliharaanStore => '$baseUrl/pemeliharaan';

  // BAST & TTD
  static String get bast => '$baseUrl/bast';
  static String bastTandaTangan(int id) => '$baseUrl/bast/$id/tanda-tangan';
  static String bastSahkan(int id) => '$baseUrl/bast/$id/sahkan';
  static String bastCetakPdf(int id) => '$baseUrl/bast/$id/cetak-pdf';

  // Depresiasi
  static String get depresiasi => '$baseUrl/depresiasi';
  static String get hitungBatchDepresiasi => '$baseUrl/depresiasi/hitung-batch';
  static String get prosesDepresiasi => '$baseUrl/depresiasi/hitung-batch';
  static String get rekomendasiPeremajaan => '$baseUrl/depresiasi/rekomendasi';

  // Notifikasi
  static String get notifikasi => '$baseUrl/notifikasi';
  static String notifikasiBaca(int id) => '$baseUrl/notifikasi/$id/baca';
  static String get notifikasiBacaSemua => '$baseUrl/notifikasi/baca-semua';

  // Laporan
  static String get laporanRingkasan => '$baseUrl/laporan/ringkasan';
  static String get laporanAset => '$baseUrl/laporan/ringkasan';
  static String get laporanAsetPdf => '$baseUrl/laporan/aset-pdf';

  // Organisasi & Master
  static String get kantor => '$baseUrl/organisasi/kantor';
  static String get divisi => '$baseUrl/organisasi/divisi';
  static String get kategori => '$baseUrl/master/kategori';
  static String get vendor => '$baseUrl/master/vendor';
  static String get kontrakSewa => '$baseUrl/master/kontrak-sewa';
  static String get pengguna => '$baseUrl/pengguna';
}
