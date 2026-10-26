class NotifikasiModel {
  final int id;
  final String judul;
  final String pesan;
  final String tipe;
  final bool sudahDibaca;
  final String? createdAt;

  NotifikasiModel({
    required this.id,
    required this.judul,
    required this.pesan,
    required this.tipe,
    required this.sudahDibaca,
    this.createdAt,
  });

  factory NotifikasiModel.fromJson(Map<String, dynamic> json) {
    return NotifikasiModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      judul: json['judul'] ?? '',
      pesan: json['pesan'] ?? '',
      tipe: json['tipe_notifikasi'] ?? '',
      sudahDibaca: json['sudah_dibaca'] == true || json['sudah_dibaca'] == 1,
      createdAt: json['created_at'],
    );
  }
}
