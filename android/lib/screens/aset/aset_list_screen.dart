import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/aset_model.dart';
import 'aset_detail_screen.dart';

class AsetListScreen extends StatefulWidget {
  const AsetListScreen({super.key});

  @override
  State<AsetListScreen> createState() => _AsetListScreenState();
}

class _AsetListScreenState extends State<AsetListScreen> with SingleTickerProviderStateMixin {
  final ApiService _apiService = ApiService();
  final NumberFormat _currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  List<AsetModel> _allAset = [];
  List<AsetModel> _filteredAset = [];
  bool _isLoading = true;
  String? _error;
  String _selectedKondisi = 'semua';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(_filterAset);
    _loadAset();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadAset() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.aset);
      final List rawList = res['data'] is List ? res['data'] : (res['data']?['data'] ?? []);
      setState(() {
        _allAset = rawList.map((item) => AsetModel.fromJson(item)).toList();
        _filterAset();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  void _filterAset() {
    final query = _searchController.text.toLowerCase().trim();
    final tabIndex = _tabController.index;

    setState(() {
      _filteredAset = _allAset.where((aset) {
        // Tab filter: 0 = Semua, 1 = Milik Sendiri, 2 = Sewa Vendor
        if (tabIndex == 1 && aset.jenisKepemilikan != 'milik_sendiri') return false;
        if (tabIndex == 2 && aset.jenisKepemilikan != 'sewa_vendor') return false;

        // Kondisi filter
        if (_selectedKondisi != 'semua' && aset.kondisi != _selectedKondisi) return false;

        // Search text
        if (query.isNotEmpty) {
          final matchNama = aset.namaAset.toLowerCase().contains(query);
          final matchNomor = aset.nomorIndex.toLowerCase().contains(query);
          final matchSeri = aset.nomorSeri?.toLowerCase().contains(query) ?? false;
          final matchMerk = aset.merk?.toLowerCase().contains(query) ?? false;
          return matchNama || matchNomor || matchSeri || matchMerk;
        }

        return true;
      }).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Inventaris Aset'),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.orangePrimary,
          labelColor: AppColors.orangePrimary,
          unselectedLabelColor: isDark ? Colors.white60 : Colors.black54,
          tabs: const [
            Tab(text: 'Semua'),
            Tab(text: 'Milik Sendiri'),
            Tab(text: 'Sewa Vendor'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Search & Filter Bar
          Container(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  onChanged: (_) => _filterAset(),
                  decoration: InputDecoration(
                    hintText: 'Cari nama, index, merk, seri...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchController.clear();
                              _filterAset();
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: isDark ? const Color(0xFF162D4A) : Colors.grey.shade100,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                // Kondisi Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('Semua Kondisi', 'semua'),
                      const SizedBox(width: 6),
                      _buildFilterChip('Sangat Baik', 'sangat_baik'),
                      const SizedBox(width: 6),
                      _buildFilterChip('Baik', 'baik'),
                      const SizedBox(width: 6),
                      _buildFilterChip('Rusak Ringan', 'rusak_ringan'),
                      const SizedBox(width: 6),
                      _buildFilterChip('Rusak Berat', 'rusak_berat'),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Asset List View
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
                            ElevatedButton.icon(
                              onPressed: _loadAset,
                              icon: const Icon(Icons.refresh),
                              label: const Text('Coba Lagi'),
                            ),
                          ],
                        ),
                      )
                    : _filteredAset.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.inbox_outlined, size: 54, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                Text(
                                  'Tidak ada aset yang sesuai',
                                  style: TextStyle(color: Colors.grey.shade600),
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadAset,
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              itemCount: _filteredAset.length,
                              itemBuilder: (context, index) {
                                final aset = _filteredAset[index];
                                return _buildAsetCard(aset, isDark);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedKondisi == value;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 11, color: isSelected ? Colors.white : null)),
      selected: isSelected,
      selectedColor: AppColors.orangePrimary,
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedKondisi = value;
            _filterAset();
          });
        }
      },
    );
  }

  Widget _buildAsetCard(AsetModel aset, bool isDark) {
    final isLeased = aset.jenisKepemilikan == 'sewa_vendor';

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => AsetDetailScreen(aset: aset),
          ),
        ).then((_) => _loadAset());
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: isLeased ? const Color(0xFF7209B7).withOpacity(0.15) : AppColors.navyPrimary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  isLeased ? Icons.business_outlined : Icons.devices_other_outlined,
                  color: isLeased ? const Color(0xFF7209B7) : AppColors.navyPrimary,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      aset.namaAset,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      aset.nomorIndex,
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.orangePrimary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    if (aset.merk != null || aset.tipeModel != null)
                      Text(
                        '${aset.merk ?? ""} ${aset.tipeModel ?? ""}'.trim(),
                        style: TextStyle(fontSize: 11, color: isDark ? Colors.white60 : Colors.black54),
                      ),
                  ],
                ),
              ),
              CustomBadge(text: aset.kondisi, type: BadgeType.kondisi),
            ],
          ),
          const Divider(height: 18),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Kantor / Penempatan',
                      style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600),
                    ),
                    Text(
                      '${aset.namaKantor ?? "-"} (${aset.namaDivisi ?? "-"})',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Flexible(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      isLeased ? 'Aset Sewa' : 'Nilai Buku',
                      style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600),
                    ),
                    Text(
                      isLeased ? (aset.namaVendor ?? 'Vendor') : _currencyFormatter.format(aset.nilaiBukuSaatIni),
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: isLeased ? const Color(0xFF7209B7) : AppColors.statusSuccess,
                      ),
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
    );
  }
}
