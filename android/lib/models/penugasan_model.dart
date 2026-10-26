class PenugasanModel {
  final int id;
  final String nomorPenugasan;
  final String jenisPenugasan;
  final String prioritas;
  final String tanggalPenugasan;
  final String tenggatWaktu;
  final String instruksi;
  final String statusPenugasan;
  final String? catatanPelaksanaan;
  final String? alasanRevisi;
  final String? namaPemberi;
  final String? namaStaf;
  final String? namaKantor;
  final List<DetailPenugasanModel> items;

  PenugasanModel({
    required this.id,
    required this.nomorPenugasan,
    required this.jenisPenugasan,
    required this.prioritas,
    required this.tanggalPenugasan,
    required this.tenggatWaktu,
    required this.instruksi,
    required this.statusPenugasan,
    this.catatanPelaksanaan,
    this.alasanRevisi,
    this.namaPemberi,
    this.namaStaf,
    this.namaKantor,
    this.items = const [],
  });

  factory PenugasanModel.fromJson(Map<String, dynamic> json) {
    return PenugasanModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nomorPenugasan: json['nomor_penugasan'] ?? '',
      jenisPenugasan: json['jenis_penugasan'] ?? '',
      prioritas: json['prioritas'] ?? 'sedang',
      tanggalPenugasan: json['tanggal_penugasan'] ?? '',
      tenggatWaktu: json['tenggat_waktu'] ?? '',
      instruksi: json['instruksi'] ?? '',
      statusPenugasan: json['status_penugasan'] ?? 'diberikan',
      catatanPelaksanaan: json['catatan_pelaksanaan'],
      alasanRevisi: json['alasan_revisi'],
      namaPemberi: json['pemberi_tugas'] != null ? json['pemberi_tugas']['nama'] : null,
      namaStaf: json['staf_pelaksana'] != null ? json['staf_pelaksana']['nama'] : null,
      namaKantor: json['kantor'] != null ? json['kantor']['nama_kantor'] : null,
      items: json['detail'] != null
          ? (json['detail'] as List).map((i) => DetailPenugasanModel.fromJson(i)).toList()
          : [],
    );
  }
}

class DetailPenugasanModel {
  final int id;
  final int asetId;
  final String? nomorIndex;
  final String? namaAset;
  final String? kondisiSebelum;
  final String? kondisiSesudah;
  final String? statusPengerjaan;

  DetailPenugasanModel({
    required this.id,
    required this.asetId,
    this.nomorIndex,
    this.namaAset,
    this.kondisiSebelum,
    this.kondisiSesudah,
    this.statusPengerjaan,
  });

  factory DetailPenugasanModel.fromJson(Map<String, dynamic> json) {
    return DetailPenugasanModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      asetId: json['aset_id'] is int ? json['aset_id'] : int.tryParse(json['aset_id'].toString()) ?? 0,
      nomorIndex: json['aset'] != null ? json['aset']['nomor_index'] : null,
      namaAset: json['aset'] != null ? json['aset']['nama_aset'] : null,
      kondisiSebelum: json['kondisi_sebelum'],
      kondisiSesudah: json['kondisi_sesudah'],
      statusPengerjaan: json['status_pengerjaan'],
    );
  }
}
