import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/pengadaan_model.dart';

class PengadaanListScreen extends StatefulWidget {
  const PengadaanListScreen({super.key});

  @override
  State<PengadaanListScreen> createState() => _PengadaanListScreenState();
}

class _PengadaanListScreenState extends State<PengadaanListScreen> {
  final ApiService _apiService = ApiService();
  final NumberFormat _currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  List<PengadaanModel> _allPengadaan = [];
  List<PengadaanModel> _filteredPengadaan = [];
  bool _isLoading = true;
  String? _error;
  String _selectedStatus = 'semua';

  @override
  void initState() {
    super.initState();
    _loadPengadaan();
  }

  Future<void> _loadPengadaan() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.pengadaan);
      final List raw = res['data'] is List ? res['data'] : (res['data']?['data'] ?? []);
      setState(() {
        _allPengadaan = raw.map((item) => PengadaanModel.fromJson(item)).toList();
        _filterPengadaan();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  void _filterPengadaan() {
    setState(() {
      if (_selectedStatus == 'semua') {
        _filteredPengadaan = List.from(_allPengadaan);
      } else {
        _filteredPengadaan = _allPengadaan.where((p) => p.statusPengadaan == _selectedStatus).toList();
      }
    });
  }

  void _showApprovalDialog(PengadaanModel item, bool disetujui) {
    final TextEditingController catatanController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(
          disetujui ? 'Setujui Pengadaan' : 'Tolak Pengadaan',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: disetujui ? AppColors.statusSuccess : AppColors.statusDanger,
          ),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Pengadaan: ${item.judulPengadaan}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            Text('Estimasi Biaya: ${_currencyFormatter.format(item.estimasiTotalBiaya)}', style: const TextStyle(color: AppColors.orangePrimary, fontSize: 12)),
            const SizedBox(height: 12),
            Text(
              disetujui
                  ? 'Konfirmasi persetujuan pengadaan barang/biaya ini selaku Kepala Kantor.'
                  : 'Berikan alasan penolakan pengadaan:',
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: catatanController,
              maxLines: 2,
              decoration: InputDecoration(
                hintText: disetujui ? 'Catatan persetujuan (opsional)' : 'Alasan penolakan pengadaan...',
                border: const OutlineInputBorder(),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: disetujui ? AppColors.statusSuccess : AppColors.statusDanger,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              try {
                await _apiService.post(
                  ApiEndpoints.pengadaanPutusan(item.id),
                  {
                    'disetujui': disetujui,
                    'catatan': catatanController.text,
                  },
                );
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(disetujui ? 'Pengadaan berhasil disetujui' : 'Pengadaan telah ditolak'),
                    ),
                  );
                }
                _loadPengadaan();
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
                  );
                }
                setState(() => _isLoading = false);
              }
            },
            child: Text(disetujui ? 'Setujui' : 'Tolak', style: const TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showDetailSheet(PengadaanModel item, bool canApprove) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          top: 20,
          left: 20,
          right: 20,
          bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade400, borderRadius: BorderRadius.circular(2))),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    item.nomorPengadaan,
                    style: const TextStyle(color: AppColors.orangePrimary, fontWeight: FontWeight.bold, fontSize: 13),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                CustomBadge(text: item.statusPengadaan, type: BadgeType.status),
              ],
            ),
            const SizedBox(height: 6),
            Text(item.judulPengadaan, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            const Divider(),
            _buildDetailRow('Unit Kantor', item.namaKantor ?? '-'),
            _buildDetailRow('Pengaju (Manajer)', item.namaPengaju ?? '-'),
            _buildDetailRow('Tanggal Diajukan', item.tanggalPengajuan),
            _buildDetailRow('Estimasi Biaya', _currencyFormatter.format(item.estimasiTotalBiaya), isAccent: true),
            const SizedBox(height: 10),
            const Text('Deskripsi Kebutuhan:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey)),
            const SizedBox(height: 4),
            Text(item.deskripsiKebutuhan, style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 10),
            const Text('Alasan Pengadaan:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey)),
            const SizedBox(height: 4),
            Text(item.alasanPengadaan, style: const TextStyle(fontSize: 13)),
            if (item.catatanPenolakan != null && item.catatanPenolakan!.isNotEmpty) ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: Colors.red.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                child: Text('Alasan Penolakan: ${item.catatanPenolakan}', style: const TextStyle(color: AppColors.statusDanger, fontSize: 12)),
              ),
            ],
            const SizedBox(height: 20),

            // Approval Buttons for Kepala Kantor
            if (canApprove && item.statusPengadaan == 'menunggu_persetujuan') ...[
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppColors.statusDanger),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      onPressed: () {
                        Navigator.pop(ctx);
                        _showApprovalDialog(item, false);
                      },
                      child: const Text('Tolak', style: TextStyle(color: AppColors.statusDanger)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.statusSuccess,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      onPressed: () {
                        Navigator.pop(ctx);
                        _showApprovalDialog(item, true);
                      },
                      child: const Text('Setujui Pengadaan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final pengguna = auth.pengguna;
    final isKepalaKantor = (pengguna?.isKepalaKantor ?? false) || (pengguna?.isAdmin ?? false);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengajuan Pengadaan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadPengadaan,
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                _buildStatusChip('Semua', 'semua'),
                const SizedBox(width: 6),
                _buildStatusChip('Menunggu Persetujuan', 'menunggu_persetujuan'),
                const SizedBox(width: 6),
                _buildStatusChip('Disetujui', 'disetujui'),
                const SizedBox(width: 6),
                _buildStatusChip('Ditolak', 'ditolak'),
              ],
            ),
          ),

          // Pengadaan List
          Expanded(
            child: _isLoading
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
                            ElevatedButton(onPressed: _loadPengadaan, child: const Text('Coba Lagi')),
                          ],
                        ),
                      )
                    : _filteredPengadaan.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.shopping_bag_outlined, size: 54, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                Text('Tidak ada pengajuan pengadaan pada filter ini', style: TextStyle(color: Colors.grey.shade600)),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadPengadaan,
                            child: ListView.builder(
                              padding: const EdgeInsets.all(16),
                              itemCount: _filteredPengadaan.length,
                              itemBuilder: (context, index) {
                                final item = _filteredPengadaan[index];
                                return _buildPengadaanCard(item, isDark, isKepalaKantor);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusChip(String label, String value) {
    final isSelected = _selectedStatus == value;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 11, color: isSelected ? Colors.white : null)),
      selected: isSelected,
      selectedColor: AppColors.orangePrimary,
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedStatus = value;
            _filterPengadaan();
          });
        }
      },
    );
  }

  Widget _buildPengadaanCard(PengadaanModel item, bool isDark, bool canApprove) {
    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      onTap: () => _showDetailSheet(item, canApprove),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  item.nomorPengadaan,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.orangePrimary),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              CustomBadge(text: item.statusPengadaan, type: BadgeType.status),
            ],
          ),
          const SizedBox(height: 8),
          Text(item.judulPengadaan, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          Text(item.deskripsiKebutuhan, maxLines: 2, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: isDark ? Colors.white70 : Colors.black87)),
          const Divider(height: 18),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Unit Kantor', style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
                    Text(
                      item.namaKantor ?? '-',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('Estimasi Biaya', style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
                  Text(_currencyFormatter.format(item.estimasiTotalBiaya), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.statusSuccess)),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isAccent = false}) {
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
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isAccent ? AppColors.statusSuccess : null),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}
