import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:printing/printing.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/bast_model.dart';

class BastDetailScreen extends StatefulWidget {
  final BastModel bast;

  const BastDetailScreen({super.key, required this.bast});

  @override
  State<BastDetailScreen> createState() => _BastDetailScreenState();
}

class _BastDetailScreenState extends State<BastDetailScreen> {
  final ApiService _apiService = ApiService();
  late BastModel _bast;
  bool _isLoading = false;
  bool _isDownloadingPdf = false;
  List<dynamic> _items = [];
  Map<String, dynamic>? _tandaTangan;

  @override
  void initState() {
    super.initState();
    _bast = widget.bast;
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiService.get('${ApiEndpoints.bast}/${_bast.id}');
      if (res['data'] != null) {
        setState(() {
          _bast = BastModel.fromJson(res['data']);
          _items = res['data']['detail'] ?? [];
          _tandaTangan = res['data']['tanda_tangan'];
        });
      }
    } catch (_) {
      // Keep existing
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _approveBast() async {
    setState(() => _isLoading = true);
    try {
      await _apiService.post(
        ApiEndpoints.bastSahkan(_bast.id),
        {'disetujui': true},
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('BAST berhasil disetujui dan difinalisasi')),
        );
      }
      _loadDetail();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _printOrDownloadPdf() async {
    setState(() => _isDownloadingPdf = true);
    try {
      final token = await _apiService.getToken();
      final url = Uri.parse(ApiEndpoints.bastCetakPdf(_bast.id));
      final response = await http.get(url, headers: {
        'Accept': 'application/pdf',
        if (token != null) 'Authorization': 'Bearer $token',
      });

      if (response.statusCode == 200) {
        await Printing.layoutPdf(
          onLayout: (_) => response.bodyBytes,
          name: 'BAST-${_bast.nomorBast.replaceAll("/", "_")}',
        );
      } else {
        throw Exception('Gagal mengunduh PDF (Status ${response.statusCode})');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.statusDanger),
        );
      }
    } finally {
      if (mounted) setState(() => _isDownloadingPdf = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final pengguna = auth.pengguna;
    final isManajerOrAdmin = (pengguna?.isManajer ?? false) || (pengguna?.isAdmin ?? false);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail BAST'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDetail,
          ),
        ],
      ),
      body: _isLoading && _items.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Main Card
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                _bast.nomorBast,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.orangePrimary),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 8),
                            CustomBadge(text: _bast.statusBast, type: BadgeType.status),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'BERITA ACARA SERAH TERIMA ASET',
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          'Jenis: ${_bast.jenisTransaksi.replaceAll("_", " ").toUpperCase()}',
                          style: const TextStyle(fontSize: 12, color: Colors.grey),
                        ),
                        const SizedBox(height: 12),
                        const Divider(),
                        _buildRow('Tanggal Dokumen', _bast.tanggalBast),
                        _buildRow('Unit Kantor', _bast.namaKantor ?? '-'),
                        _buildRow('Pihak Yang Menyerahkan', _bast.namaPenyerah ?? '-'),
                        _buildRow('Pihak Yang Menerima', _bast.namaPenerima ?? '-'),
                        _buildRow('Disetujui Manajer', _bast.namaManajer ?? 'Belum disetujui'),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Electronic Signature Info
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.statusSuccess.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.statusSuccess.withOpacity(0.3)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.verified_user_rounded, color: AppColors.statusSuccess, size: 32),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Integritas Tanda Tangan Elektronik', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.statusSuccess)),
                              const SizedBox(height: 2),
                              Text(
                                _tandaTangan != null
                                    ? 'Ditandatangani oleh ${_tandaTangan!['penanda_tangan']?['nama'] ?? "Petugas"} (${_tandaTangan!['waktu_tanda_tangan'] ?? ""})'
                                    : 'Dokumen tercatat resmi dalam sistem SI-ASET dengan hash audit.',
                                style: const TextStyle(fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Transferred Assets List
                  const Text('RINCIAN ASET YANG DISERAHTERIMAKAN', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 0.8)),
                  const SizedBox(height: 8),
                  if (_items.isEmpty)
                    const Text('Belum ada rincian aset.', style: TextStyle(fontStyle: FontStyle.italic, color: Colors.grey))
                  else
                    ..._items.map((item) => GlassCard(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(12),
                          child: Row(
                            children: [
                              const Icon(Icons.inventory_2_outlined, color: AppColors.navyPrimary),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(item['aset']?['nama_aset'] ?? 'Aset #${item['aset_id']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                    Text(item['aset']?['nomor_index'] ?? '-', style: const TextStyle(color: AppColors.orangePrimary, fontSize: 11)),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(color: Colors.blueGrey.withOpacity(0.1), borderRadius: BorderRadius.circular(4)),
                                child: Text('Kondisi: ${item['kondisi_saat_serah'] ?? "baik"}', style: const TextStyle(fontSize: 11)),
                              ),
                            ],
                          ),
                        )),

                  const SizedBox(height: 24),

                  // Print & Download PDF
                  ElevatedButton.icon(
                    onPressed: _isDownloadingPdf ? null : _printOrDownloadPdf,
                    icon: _isDownloadingPdf
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.print_rounded, color: Colors.white),
                    label: Text(
                      _isDownloadingPdf ? 'Mempersiapkan Dokumen...' : 'Cetak / Unduh PDF BAST Resmi',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF7209B7),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),

                  // Approval for Manager if waiting
                  if (isManajerOrAdmin && (_bast.statusBast == 'menunggu_persetujuan' || _bast.statusBast == 'draft')) ...[
                    const SizedBox(height: 12),
                    ElevatedButton.icon(
                      onPressed: _approveBast,
                      icon: const Icon(Icons.check_circle_outline, color: Colors.white),
                      label: const Text('Setujui & Sahkan BAST Ini', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.statusSuccess,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _buildRow(String label, String value) {
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
}
