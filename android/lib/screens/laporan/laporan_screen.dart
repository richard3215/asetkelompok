import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:printing/printing.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/glass_card.dart';

class LaporanScreen extends StatefulWidget {
  const LaporanScreen({super.key});

  @override
  State<LaporanScreen> createState() => _LaporanScreenState();
}

class _LaporanScreenState extends State<LaporanScreen> {
  final ApiService _apiService = ApiService();
  final NumberFormat _currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  bool _isLoading = true;
  bool _isDownloadingPdf = false;
  Map<String, dynamic>? _summary;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadSummary();
  }

  Future<void> _loadSummary() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.laporanAset);
      setState(() {
        _summary = res['ringkasan_eksekutif'] ?? res['data'] ?? {};
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _downloadPdf() async {
    setState(() => _isDownloadingPdf = true);
    try {
      final token = await _apiService.getToken();
      final url = Uri.parse(ApiEndpoints.laporanAsetPdf);
      final response = await http.get(url, headers: {
        'Accept': 'application/pdf',
        if (token != null) 'Authorization': 'Bearer $token',
      });

      if (response.statusCode == 200) {
        await Printing.layoutPdf(
          onLayout: (_) => response.bodyBytes,
          name: 'Laporan_Eksekutif_Aset_${DateFormat("yyyyMMdd").format(DateTime.now())}',
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

  Future<void> _runDepresiasi() async {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Jalankan Depresiasi Bulanan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: const Text('Sistem akan menghitung penyusutan garis lurus untuk seluruh aset milik perusahaan pada periode berjalan. Lanjutkan?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.orangePrimary),
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              try {
                final res = await _apiService.post(ApiEndpoints.prosesDepresiasi);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(res['message'] ?? 'Proses depresiasi selesai')),
                  );
                }
                _loadSummary();
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
                  );
                }
                setState(() => _isLoading = false);
              }
            },
            child: const Text('Jalankan', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Future<void> _showRejuvenationList() async {
    try {
      final res = await _apiService.get(ApiEndpoints.rekomendasiPeremajaan);
      final List raw = res['data'] is List ? res['data'] : (res['data']?['data'] ?? []);

      if (!mounted) return;

      showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
        builder: (ctx) => DraggableScrollableSheet(
          initialChildSize: 0.6,
          maxChildSize: 0.9,
          minChildSize: 0.4,
          expand: false,
          builder: (_, controller) => Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade400, borderRadius: BorderRadius.circular(2))),
                ),
                const SizedBox(height: 16),
                const Text('Rekomendasi Peremajaan Aset', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                const SizedBox(height: 4),
                const Text('Aset yang mendekati atau telah habis umur manfaatnya:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                const SizedBox(height: 14),
                Expanded(
                  child: raw.isEmpty
                      ? const Center(child: Text('Tidak ada rekomendasi peremajaan saat ini.'))
                      : ListView.builder(
                          controller: controller,
                          itemCount: raw.length,
                          itemBuilder: (context, i) {
                            final item = raw[i];
                            return GlassCard(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.all(12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Text(
                                          item['aset']?['nama_aset'] ?? 'Aset #${item['aset_id']}',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                        decoration: BoxDecoration(color: Colors.red.withOpacity(0.15), borderRadius: BorderRadius.circular(4)),
                                        child: Text(
                                          (item['prioritas'] ?? 'tinggi').toString().toUpperCase(),
                                          style: const TextStyle(color: AppColors.statusDanger, fontSize: 10, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(item['aset']?['nomor_index'] ?? '-', style: const TextStyle(color: AppColors.orangePrimary, fontSize: 11)),
                                  const SizedBox(height: 4),
                                  Text('Alasan: ${item['alasan_rekomendasi'] ?? "-"}', style: const TextStyle(fontSize: 12)),
                                ],
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
          ),
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final totalAset = _summary?['total_aset'] ?? 0;
    final totalPerolehan = double.tryParse(_summary?['total_nilai_perolehan']?.toString() ?? '0') ?? 0.0;
    final totalPenyusutan = double.tryParse(_summary?['total_akumulasi_penyusutan']?.toString() ?? '0') ?? 0.0;
    final totalNilaiBuku = double.tryParse(_summary?['total_nilai_buku']?.toString() ?? '0') ?? 0.0;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Laporan & Eksekutif'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadSummary),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 48, color: AppColors.statusDanger),
                      const SizedBox(height: 12),
                      Text(_error!, textAlign: TextAlign.center),
                      const SizedBox(height: 12),
                      ElevatedButton(onPressed: _loadSummary, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Executive Banner
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          gradient: AppColors.navyGradient,
                          borderRadius: BorderRadius.circular(18),
                          boxShadow: [
                            BoxShadow(color: AppColors.navyDark.withOpacity(0.3), blurRadius: 12, offset: const Offset(0, 6)),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('RINGKASAN EKSEKUTIF ASET', style: TextStyle(color: AppColors.orangePrimary, fontWeight: FontWeight.bold, fontSize: 11, letterSpacing: 1.2)),
                            const SizedBox(height: 6),
                            const Text('Valuasi & Inventarisasi Perusahaan', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                            const SizedBox(height: 14),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text('Total Populasi Aset', style: TextStyle(color: Colors.white70, fontSize: 11)),
                                      Text('$totalAset Unit', style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      const Text('Total Nilai Buku Saat Ini', style: TextStyle(color: Colors.white70, fontSize: 11)),
                                      Text(
                                        _currencyFormatter.format(totalNilaiBuku),
                                        style: const TextStyle(color: AppColors.statusSuccess, fontSize: 16, fontWeight: FontWeight.bold),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Metric Details Grid
                      Row(
                        children: [
                          Expanded(
                            child: GlassCard(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Nilai Perolehan Awal', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                  const SizedBox(height: 4),
                                  Text(
                                    _currencyFormatter.format(totalPerolehan),
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: GlassCard(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Akumulasi Depresiasi', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                  const SizedBox(height: 4),
                                  Text(
                                    '- ${_currencyFormatter.format(totalPenyusutan)}',
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.statusDanger),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 20),

                      // Operational Actions
                      const Text('TINDAKAN MANAJERIAL & PELAPORAN', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 0.8)),
                      const SizedBox(height: 10),

                      // Export PDF button
                      ElevatedButton.icon(
                        onPressed: _isDownloadingPdf ? null : _downloadPdf,
                        icon: _isDownloadingPdf
                            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.picture_as_pdf_rounded, color: Colors.white),
                        label: Text(
                          _isDownloadingPdf ? 'Menghasilkan PDF...' : 'Unduh Laporan PDF Eksekutif',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF7209B7),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),

                      const SizedBox(height: 10),

                      // Rejuvenation list button
                      OutlinedButton.icon(
                        onPressed: _showRejuvenationList,
                        icon: const Icon(Icons.restart_alt_rounded, color: AppColors.orangePrimary),
                        label: const Text('Evaluasi & Rekomendasi Peremajaan', style: TextStyle(color: AppColors.orangePrimary, fontWeight: FontWeight.bold)),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: AppColors.orangePrimary),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),

                      const SizedBox(height: 10),

                      // Batch monthly depreciation button
                      OutlinedButton.icon(
                        onPressed: _runDepresiasi,
                        icon: const Icon(Icons.calculate_outlined, color: AppColors.navyPrimary),
                        label: const Text('Proses Depresiasi Bulanan Otomatis', style: TextStyle(color: AppColors.navyPrimary, fontWeight: FontWeight.bold)),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: AppColors.navyPrimary),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }
}
