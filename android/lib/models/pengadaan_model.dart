class PengadaanModel {
  final int id;
  final String nomorPengadaan;
  final String judulPengadaan;
  final String deskripsiKebutuhan;
  final String alasanPengadaan;
  final double estimasiTotalBiaya;
  final String statusPengadaan;
  final String tanggalPengajuan;
  final String? namaPengaju;
  final String? namaKantor;
  final String? catatanPenolakan;

  PengadaanModel({
    required this.id,
    required this.nomorPengadaan,
    required this.judulPengadaan,
    required this.deskripsiKebutuhan,
    required this.alasanPengadaan,
    required this.estimasiTotalBiaya,
    required this.statusPengadaan,
    required this.tanggalPengajuan,
    this.namaPengaju,
    this.namaKantor,
    this.catatanPenolakan,
  });

  factory PengadaanModel.fromJson(Map<String, dynamic> json) {
    return PengadaanModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nomorPengadaan: json['nomor_pengadaan'] ?? '',
      judulPengadaan: json['judul_pengadaan'] ?? '',
      deskripsiKebutuhan: json['deskripsi_kebutuhan'] ?? '',
      alasanPengadaan: json['alasan_pengadaan'] ?? '',
      estimasiTotalBiaya: double.tryParse(json['estimasi_total_biaya']?.toString() ?? '0') ?? 0.0,
      statusPengadaan: json['status_pengadaan'] ?? 'draft',
      tanggalPengajuan: json['tanggal_pengajuan'] ?? '',
      namaPengaju: json['pengaju'] != null ? json['pengaju']['nama'] : null,
      namaKantor: json['kantor'] != null ? json['kantor']['nama_kantor'] : null,
      catatanPenolakan: json['catatan_penolakan'],
    );
  }
}
