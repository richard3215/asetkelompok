import 'package:flutter/material.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/bast_model.dart';
import 'bast_detail_screen.dart';

class BastListScreen extends StatefulWidget {
  const BastListScreen({super.key});

  @override
  State<BastListScreen> createState() => _BastListScreenState();
}

class _BastListScreenState extends State<BastListScreen> {
  final ApiService _apiService = ApiService();

  List<BastModel> _allBast = [];
  List<BastModel> _filteredBast = [];
  bool _isLoading = true;
  String? _error;
  String _selectedStatus = 'semua';

  @override
  void initState() {
    super.initState();
    _loadBast();
  }

  Future<void> _loadBast() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.bast);
      final List raw = res['data'] is List ? res['data'] : (res['data']?['data'] ?? []);
      setState(() {
        _allBast = raw.map((item) => BastModel.fromJson(item)).toList();
        _filterBast();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  void _filterBast() {
    setState(() {
      if (_selectedStatus == 'semua') {
        _filteredBast = List.from(_allBast);
      } else {
        _filteredBast = _allBast.where((b) => b.statusBast == _selectedStatus).toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Berita Acara (BAST)'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadBast,
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
                _buildFilterChip('Semua', 'semua'),
                const SizedBox(width: 6),
                _buildFilterChip('Draft', 'draft'),
                const SizedBox(width: 6),
                _buildFilterChip('Menunggu Persetujuan', 'menunggu_persetujuan'),
                const SizedBox(width: 6),
                _buildFilterChip('Disetujui', 'disetujui'),
                const SizedBox(width: 6),
                _buildFilterChip('Final & Sah', 'final'),
              ],
            ),
          ),

          // List
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
                            ElevatedButton(onPressed: _loadBast, child: const Text('Coba Lagi')),
                          ],
                        ),
                      )
                    : _filteredBast.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.description_outlined, size: 54, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                Text('Tidak ada dokumen BAST pada filter ini', style: TextStyle(color: Colors.grey.shade600)),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadBast,
                            child: ListView.builder(
                              padding: const EdgeInsets.all(16),
                              itemCount: _filteredBast.length,
                              itemBuilder: (context, index) {
                                final bast = _filteredBast[index];
                                return _buildBastCard(bast);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedStatus == value;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 11, color: isSelected ? Colors.white : null)),
      selected: isSelected,
      selectedColor: AppColors.orangePrimary,
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedStatus = value;
            _filterBast();
          });
        }
      },
    );
  }

  Widget _buildBastCard(BastModel bast) {
    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => BastDetailScreen(bast: bast)),
        ).then((_) => _loadBast());
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  bast.nomorBast,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.orangePrimary),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              CustomBadge(text: bast.statusBast, type: BadgeType.status),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            'BAST ${bast.jenisTransaksi.replaceAll('_', ' ').toUpperCase()}',
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
          ),
          if (bast.catatan != null) ...[
            const SizedBox(height: 4),
            Text(bast.catatan!, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12)),
          ],
          const Divider(height: 18),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  'Penyerah: ${bast.namaPenyerah ?? "-"}',
                  style: const TextStyle(fontSize: 11, color: Colors.grey),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Penerima: ${bast.namaPenerima ?? "-"}',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
                  textAlign: TextAlign.end,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
