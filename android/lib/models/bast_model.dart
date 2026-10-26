class BastModel {
  final int id;
  final String nomorBast;
  final String tanggalBast;
  final String jenisTransaksi;
  final String statusBast;
  final String? catatan;
  final String? namaPenyerah;
  final String? namaPenerima;
  final String? namaManajer;
  final String? namaKantor;

  BastModel({
    required this.id,
    required this.nomorBast,
    required this.tanggalBast,
    required this.jenisTransaksi,
    required this.statusBast,
    this.catatan,
    this.namaPenyerah,
    this.namaPenerima,
    this.namaManajer,
    this.namaKantor,
  });

  factory BastModel.fromJson(Map<String, dynamic> json) {
    return BastModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nomorBast: json['nomor_bast'] ?? '',
      tanggalBast: json['tanggal_bast'] ?? '',
      jenisTransaksi: json['jenis_transaksi'] ?? '',
      statusBast: json['status_bast'] ?? 'draft',
      catatan: json['catatan'],
      namaPenyerah: json['pihak_penyerah'] != null ? json['pihak_penyerah']['nama'] : null,
      namaPenerima: json['pihak_penerima'] != null ? json['pihak_penerima']['nama'] : null,
      namaManajer: json['disetujui_manajer'] != null ? json['disetujui_manajer']['nama'] : null,
      namaKantor: json['kantor'] != null ? json['kantor']['nama_kantor'] : null,
    );
  }
}
