import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/aset_model.dart';
import '../penugasan/penugasan_form_screen.dart';

class AsetDetailScreen extends StatefulWidget {
  final AsetModel aset;

  const AsetDetailScreen({super.key, required this.aset});

  @override
  State<AsetDetailScreen> createState() => _AsetDetailScreenState();
}

class _AsetDetailScreenState extends State<AsetDetailScreen> {
  final ApiService _apiService = ApiService();
  final NumberFormat _currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  late AsetModel _aset;
  bool _isLoading = false;
  List<dynamic> _riwayatList = [];

  @override
  void initState() {
    super.initState();
    _aset = widget.aset;
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiService.get('${ApiEndpoints.aset}/${_aset.id}');
      if (res['data'] != null) {
        setState(() {
          _aset = AsetModel.fromJson(res['data']);
          _riwayatList = res['data']['riwayat'] ?? [];
        });
      }
    } catch (_) {
      // Ignore error and retain passed asset
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showOpnameDialog() {
    String selectedKondisi = _aset.kondisi;
    String selectedStatusInventaris = 'sesuai_lokasi';
    final TextEditingController catatanController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Inventarisasi Fisik (Opname)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Aset: ${_aset.namaAset}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                Text('Index: ${_aset.nomorIndex}', style: const TextStyle(color: AppColors.orangePrimary, fontSize: 12)),
                const SizedBox(height: 14),

                const Text('Kondisi Fisik Saat Ini:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                DropdownButton<String>(
                  value: selectedKondisi,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 'sangat_baik', child: Text('Sangat Baik')),
                    DropdownMenuItem(value: 'baik', child: Text('Baik')),
                    DropdownMenuItem(value: 'rusak_ringan', child: Text('Rusak Ringan')),
                    DropdownMenuItem(value: 'rusak_berat', child: Text('Rusak Berat')),
                    DropdownMenuItem(value: 'tidak_ekonomis', child: Text('Tidak Ekonomis')),
                  ],
                  onChanged: (val) {
                    if (val != null) setDialogState(() => selectedKondisi = val);
                  },
                ),
                const SizedBox(height: 12),

                const Text('Hasil Pemeriksaan:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                DropdownButton<String>(
                  value: selectedStatusInventaris,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 'sesuai_lokasi', child: Text('Ditemukan & Sesuai Lokasi')),
                    DropdownMenuItem(value: 'beda_lokasi', child: Text('Ditemukan di Lokasi Lain')),
                    DropdownMenuItem(value: 'rusak', child: Text('Ditemukan Dalam Keadaan Rusak')),
                    DropdownMenuItem(value: 'hilang', child: Text('Tidak Ditemukan / Hilang')),
                  ],
                  onChanged: (val) {
                    if (val != null) setDialogState(() => selectedStatusInventaris = val);
                  },
                ),
                const SizedBox(height: 12),

                TextField(
                  controller: catatanController,
                  maxLines: 2,
                  decoration: const InputDecoration(
                    labelText: 'Catatan Pemeriksaan',
                    hintText: 'Misal: Kabel adaptor diganti, stiker label utuh',
                    border: OutlineInputBorder(),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.orangePrimary),
              onPressed: () async {
                Navigator.pop(ctx);
                try {
                  await _apiService.post(
                    ApiEndpoints.asetInventarisasi(_aset.id),
                    {
                      'kondisi': selectedKondisi,
                      'status_inventaris': selectedStatusInventaris,
                      'catatan': catatanController.text,
                    },
                  );
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Hasil inventarisasi berhasil disimpan')),
                    );
                  }
                  _loadDetail();
                } catch (e) {
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
                    );
                  }
                }
              },
              child: const Text('Simpan Opname', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isLeased = _aset.jenisKepemilikan == 'sewa_vendor';
    final auth = context.watch<AuthProvider>();
    final canAssignTask = auth.pengguna?.isManajer == true || auth.pengguna?.isAdmin == true;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Aset'),
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_2_rounded),
            tooltip: 'Tampilkan QR Code',
            onPressed: () => _showQrModal(context),
          ),
        ],
      ),
      body: _isLoading && _riwayatList.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Main Info Card
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              width: 54,
                              height: 54,
                              decoration: BoxDecoration(
                                gradient: isLeased ? null : AppColors.navyGradient,
                                color: isLeased ? const Color(0xFF7209B7) : null,
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: const Icon(Icons.devices, color: Colors.white, size: 28),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _aset.namaAset,
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 3),
                                  SelectableText(
                                    _aset.nomorIndex,
                                    style: const TextStyle(
                                      color: AppColors.orangePrimary,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Wrap(
                                    spacing: 8,
                                    runSpacing: 4,
                                    children: [
                                      CustomBadge(text: _aset.kondisi, type: BadgeType.kondisi),
                                      CustomBadge(text: _aset.statusOperasional, type: BadgeType.status),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        const Divider(),
                        const SizedBox(height: 8),
                        _buildInfoRow('Jenis Kepemilikan', isLeased ? 'Sewa dari Vendor' : 'Milik Perusahaan'),
                        _buildInfoRow('Kategori', _aset.namaKategori ?? '-'),
                        _buildInfoRow('Merk / Model', '${_aset.merk ?? "-"} / ${_aset.tipeModel ?? "-"}'),
                        _buildInfoRow('Nomor Seri', _aset.nomorSeri ?? '-'),
                        if (isLeased) _buildInfoRow('Vendor Penyedia', _aset.namaVendor ?? '-'),
                        if (_aset.barcodeVendor != null) _buildInfoRow('Barcode Vendor', _aset.barcodeVendor!),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Financial Card (Depreciation & Valuation)
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.account_balance_wallet_outlined, size: 20, color: AppColors.orangePrimary),
                            const SizedBox(width: 8),
                            const Text(
                              'Nilai & Penyusutan Aset',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            const Spacer(),
                            if (isLeased)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.purple.withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'Bebas Depresiasi',
                                  style: TextStyle(color: Colors.purple, fontSize: 10, fontWeight: FontWeight.bold),
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        if (isLeased)
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 8),
                            child: Text(
                              'Aset sewa dibiayai berdasarkan kontrak berkala dan tidak termasuk dalam perhitungan akumulasi depresiasi aset milik perusahaan.',
                              style: TextStyle(fontSize: 12, fontStyle: FontStyle.italic),
                            ),
                          )
                        else ...[
                          _buildFinancialTile('Nilai Perolehan Awal', _currencyFormatter.format(_aset.nilaiPerolehan), Colors.blueGrey),
                          _buildFinancialTile('Akumulasi Penyusutan', '- ${_currencyFormatter.format(_aset.akumulasiPenyusutan)}', AppColors.statusDanger),
                          _buildFinancialTile('Nilai Buku Saat Ini', _currencyFormatter.format(_aset.nilaiBukuSaatIni), AppColors.statusSuccess, isBold: true),
                        ],
                        const SizedBox(height: 6),
                        _buildInfoRow('Tanggal Perolehan', _aset.tanggalPerolehan),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Location & Custodian
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.location_on_outlined, size: 20, color: AppColors.navyPrimary),
                            SizedBox(width: 8),
                            Text('Penempatan & Penanggung Jawab', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _buildInfoRow('Kantor Cabang/Unit', _aset.namaKantor ?? '-'),
                        _buildInfoRow('Divisi / Bagian', _aset.namaDivisi ?? '-'),
                        _buildInfoRow('Penanggung Jawab', _aset.namaPenanggungJawab ?? '-'),
                        _buildInfoRow('Status Inventaris', _aset.statusInventaris.replaceAll('_', ' ').toUpperCase()),
                      ],
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Action Button: Opname Fisik
                  ElevatedButton.icon(
                    onPressed: _showOpnameDialog,
                    icon: const Icon(Icons.fact_check_outlined, color: Colors.white),
                    label: const Text('Lakukan Inventarisasi Fisik / Opname', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.orangePrimary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),

                  // Action Button for Manager: Tugaskan Staf untuk Aset Ini
                  if (canAssignTask) ...[
                    const SizedBox(height: 10),
                    OutlinedButton.icon(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => PenugasanFormScreen(preselectedAsetId: _aset.id),
                          ),
                        );
                      },
                      icon: const Icon(Icons.assignment_ind_rounded, color: AppColors.orangePrimary),
                      label: const Text(
                        'Tugaskan Pemeriksaan / Servis Aset Ini',
                        style: TextStyle(color: AppColors.orangePrimary, fontWeight: FontWeight.bold),
                      ),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppColors.orangePrimary),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],

                  const SizedBox(height: 20),

                  // Audit Trail (Riwayat Mutasi & Perubahan)
                  if (_riwayatList.isNotEmpty) ...[
                    const Text('RIWAYAT AUDIT & PERUBAHAN ASET', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 0.8)),
                    const SizedBox(height: 10),
                    ..._riwayatList.map((riwayat) => _buildRiwayatItem(riwayat, isDark)),
                  ],
                ],
              ),
            ),
    );
  }

  void _showQrModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade400, borderRadius: BorderRadius.circular(2))),
            const SizedBox(height: 16),
            const Text('Label QR Code Resmi Aset', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            const SizedBox(height: 6),
            Text(_aset.namaAset, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13)),
            Text(_aset.nomorIndex, style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.orangePrimary)),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: QrImageView(
                data: _aset.nomorIndex,
                version: QrVersions.auto,
                size: 180.0,
                eyeStyle: const QrEyeStyle(eyeShape: QrEyeShape.square, color: AppColors.navyPrimary),
                dataModuleStyle: const QrDataModuleStyle(dataModuleShape: QrDataModuleShape.square, color: AppColors.navyPrimary),
              ),
            ),
            const SizedBox(height: 14),
            const Text('Dapat dipindai oleh modul scan SI-ASET di Android', style: TextStyle(fontSize: 11, color: Colors.grey)),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFinancialTile(String label, String value, Color color, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: 12, fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: TextStyle(fontSize: isBold ? 14 : 12, fontWeight: isBold ? FontWeight.bold : FontWeight.w600, color: color),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRiwayatItem(dynamic r, bool isDark) {
    return GlassCard(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                (r['jenis_kegiatan'] ?? 'perubahan').toString().toUpperCase().replaceAll('_', ' '),
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: AppColors.orangePrimary),
              ),
              Text(r['tanggal_perubahan'] ?? '', style: const TextStyle(fontSize: 10, color: Colors.grey)),
            ],
          ),
          const SizedBox(height: 4),
          Text(r['keterangan'] ?? '-', style: const TextStyle(fontSize: 12)),
          if (r['pelaksana'] != null)
            Text('Oleh: ${r['pelaksana']['nama'] ?? "-"}', style: const TextStyle(fontSize: 10, fontStyle: FontStyle.italic)),
        ],
      ),
    );
  }
}
