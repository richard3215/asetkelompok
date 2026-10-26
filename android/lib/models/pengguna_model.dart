class PenggunaModel {
  final int id;
  final String nip;
  final String nama;
  final String email;
  final String? nomorTelepon;
  final String? fotoProfil;
  final PeranModel? peran;
  final KantorModel? kantor;
  final DivisiModel? divisi;
  final List<String> hakAkses;

  PenggunaModel({
    required this.id,
    required this.nip,
    required this.nama,
    required this.email,
    this.nomorTelepon,
    this.fotoProfil,
    this.peran,
    this.kantor,
    this.divisi,
    this.hakAkses = const [],
  });

  String? get telepon => nomorTelepon;

  factory PenggunaModel.fromJson(Map<String, dynamic> json) {
    return PenggunaModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nip: json['nip'] ?? '',
      nama: json['nama'] ?? '',
      email: json['email'] ?? '',
      nomorTelepon: json['nomor_telepon'],
      fotoProfil: json['foto_profil'],
      peran: json['peran'] != null ? PeranModel.fromJson(json['peran']) : null,
      kantor: json['kantor'] != null ? KantorModel.fromJson(json['kantor']) : null,
      divisi: json['divisi'] != null ? DivisiModel.fromJson(json['divisi']) : null,
      hakAkses: json['hak_akses'] != null ? List<String>.from(json['hak_akses']) : [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nip': nip,
      'nama': nama,
      'email': email,
      'nomor_telepon': nomorTelepon,
      'foto_profil': fotoProfil,
      'peran': peran?.toJson(),
      'kantor': kantor?.toJson(),
      'divisi': divisi?.toJson(),
      'hak_akses': hakAkses,
    };
  }

  bool hasRole(String roleCode) => peran?.kode == roleCode;
  bool get isAdmin => hasRole('admin');
  bool get isKepalaKantor => hasRole('kepala_kantor');
  bool get isManajer => hasRole('manajer');
  bool get isStaf => hasRole('staf');
}

class PeranModel {
  final int id;
  final String kode;
  final String nama;

  PeranModel({required this.id, required this.kode, required this.nama});

  factory PeranModel.fromJson(Map<String, dynamic> json) {
    return PeranModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      kode: json['kode'] ?? json['kode_peran'] ?? '',
      nama: json['nama'] ?? json['nama_peran'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'kode': kode, 'nama': nama};
}

class KantorModel {
  final int id;
  final String kode;
  final String nama;
  final String jenis;

  KantorModel({required this.id, required this.kode, required this.nama, required this.jenis});

  factory KantorModel.fromJson(Map<String, dynamic> json) {
    return KantorModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      kode: json['kode'] ?? json['kode_kantor'] ?? '',
      nama: json['nama'] ?? json['nama_kantor'] ?? '',
      jenis: json['jenis'] ?? json['jenis_kantor'] ?? 'cabang',
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'kode': kode, 'nama': nama, 'jenis': jenis};
}

class DivisiModel {
  final int id;
  final String kode;
  final String nama;

  DivisiModel({required this.id, required this.kode, required this.nama});

  factory DivisiModel.fromJson(Map<String, dynamic> json) {
    return DivisiModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      kode: json['kode'] ?? json['kode_divisi'] ?? '',
      nama: json['nama'] ?? json['nama_divisi'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'kode': kode, 'nama': nama};
}
