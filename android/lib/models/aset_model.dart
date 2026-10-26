class AsetModel {
  final int id;
  final String nomorIndex;
  final String? nomorSeri;
  final String? barcodeVendor;
  final String namaAset;
  final String jenisKepemilikan; // milik_sendiri | sewa_vendor
  final String? merk;
  final String? tipeModel;
  final String kondisi; // sangat_baik, baik, rusak_ringan, rusak_berat, tidak_ekonomis
  final String statusOperasional;
  final double nilaiPerolehan;
  final double nilaiBukuSaatIni;
  final double akumulasiPenyusutan;
  final String tanggalPerolehan;
  final String? fotoAset;
  final String? spesifikasiTeknis;
  final String statusInventaris;
  final String? namaKategori;
  final String? namaKantor;
  final String? namaDivisi;
  final String? namaPenanggungJawab;
  final String? namaVendor;

  AsetModel({
    required this.id,
    required this.nomorIndex,
    this.nomorSeri,
    this.barcodeVendor,
    required this.namaAset,
    required this.jenisKepemilikan,
    this.merk,
    this.tipeModel,
    required this.kondisi,
    required this.statusOperasional,
    required this.nilaiPerolehan,
    required this.nilaiBukuSaatIni,
    required this.akumulasiPenyusutan,
    required this.tanggalPerolehan,
    this.fotoAset,
    this.spesifikasiTeknis,
    required this.statusInventaris,
    this.namaKategori,
    this.namaKantor,
    this.namaDivisi,
    this.namaPenanggungJawab,
    this.namaVendor,
  });

  factory AsetModel.fromJson(Map<String, dynamic> json) {
    return AsetModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nomorIndex: json['nomor_index'] ?? '',
      nomorSeri: json['nomor_seri'],
      barcodeVendor: json['barcode_vendor'],
      namaAset: json['nama_aset'] ?? '',
      jenisKepemilikan: json['jenis_kepemilikan'] ?? 'milik_sendiri',
      merk: json['merk'],
      tipeModel: json['tipe_model'],
      kondisi: json['kondisi'] ?? 'baik',
      statusOperasional: json['status_operasional'] ?? 'aktif',
      nilaiPerolehan: double.tryParse(json['nilai_perolehan']?.toString() ?? '0') ?? 0.0,
      nilaiBukuSaatIni: double.tryParse(json['nilai_buku_saat_ini']?.toString() ?? '0') ?? 0.0,
      akumulasiPenyusutan: double.tryParse(json['akumulasi_penyusutan']?.toString() ?? '0') ?? 0.0,
      tanggalPerolehan: json['tanggal_perolehan'] ?? '',
      fotoAset: json['foto_aset'],
      spesifikasiTeknis: json['spesifikasi_teknis'],
      statusInventaris: json['status_inventaris'] ?? 'belum_diperiksa',
      namaKategori: json['kategori'] != null ? json['kategori']['nama_kategori'] : null,
      namaKantor: json['kantor'] != null ? json['kantor']['nama_kantor'] : null,
      namaDivisi: json['divisi'] != null ? json['divisi']['nama_divisi'] : null,
      namaPenanggungJawab: json['penanggung_jawab'] != null ? json['penanggung_jawab']['nama'] : null,
      namaVendor: json['vendor'] != null ? json['vendor']['nama_vendor'] : null,
    );
  }
}
