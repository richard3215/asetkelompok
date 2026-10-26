import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/aset_model.dart';
import '../../models/pengguna_model.dart';

class PenugasanFormScreen extends StatefulWidget {
  final int? preselectedAsetId;

  const PenugasanFormScreen({super.key, this.preselectedAsetId});

  @override
  State<PenugasanFormScreen> createState() => _PenugasanFormScreenState();
}

class _PenugasanFormScreenState extends State<PenugasanFormScreen> {
  final ApiService _apiService = ApiService();
  final _formKey = GlobalKey<FormState>();

  final TextEditingController _instruksiController = TextEditingController();
  final DateFormat _dateFormat = DateFormat('yyyy-MM-dd');

  bool _isLoadingData = true;
  bool _isSubmitting = false;
  String? _loadError;

  List<PenggunaModel> _stafList = [];
  List<AsetModel> _allAsetList = [];
  List<dynamic> _kantorList = [];

  int? _selectedStafId;
  int? _selectedKantorId;
  String _selectedJenis = 'pemeriksaan_kondisi';
  String _selectedPrioritas = 'sedang';
  DateTime _tanggalPenugasan = DateTime.now();
  DateTime _tenggatWaktu = DateTime.now().add(const Duration(days: 7));
  final Set<int> _selectedAsetIds = {};

  final Map<String, String> _jenisLabels = {
    'pemeriksaan_kondisi': 'Pemeriksaan Kondisi Rutin',
    'inventarisasi': 'Inventarisasi / Opname Fisik',
    'pemeliharaan': 'Pemeliharaan & Servis Teknis',
    'penggantian': 'Penggantian Komponen/Perangkat',
    'penarikan': 'Penarikan Aset ke Gudang',
    'pengembalian': 'Pengembalian Aset ke Unit',
    'pemindahan': 'Pemindahan / Relokasi Aset',
    'pelabelan_qr': 'Pelabelan Fisik QR / Barcode',
  };

  final Map<String, IconData> _jenisIcons = {
    'pemeriksaan_kondisi': Icons.fact_check_outlined,
    'inventarisasi': Icons.inventory_2_outlined,
    'pemeliharaan': Icons.build_circle_outlined,
    'penggantian': Icons.swap_horiz_rounded,
    'penarikan': Icons.archive_outlined,
    'pengembalian': Icons.unarchive_outlined,
    'pemindahan': Icons.move_up_rounded,
    'pelabelan_qr': Icons.qr_code_2_rounded,
  };

  @override
  void initState() {
    super.initState();
    if (widget.preselectedAsetId != null) {
      _selectedAsetIds.add(widget.preselectedAsetId!);
    }
    _fetchFormData();
  }

  @override
  void dispose() {
    _instruksiController.dispose();
    super.dispose();
  }

  Future<void> _fetchFormData() async {
    setState(() {
      _isLoadingData = true;
      _loadError = null;
    });

    try {
      final auth = context.read<AuthProvider>();
      final pengguna = auth.pengguna;

      // 1. Ambil daftar staf pelaksana
      final resStaf = await _apiService.get('${ApiEndpoints.pengguna}?peran=staf&semua=1');
      List rawStaf = [];
      if (resStaf['data'] is List) {
        rawStaf = resStaf['data'];
      } else if (resStaf['data']?['data'] is List) {
        rawStaf = resStaf['data']['data'];
      }
      _stafList = rawStaf.map((item) => PenggunaModel.fromJson(item)).toList();

      // Jika kosong (mungkin staf belum difilter peran), ambil semua pengguna dan filter staf
      if (_stafList.isEmpty) {
        final resAllUser = await _apiService.get('${ApiEndpoints.pengguna}?semua=1');
        List rawUsers = resAllUser['data'] is List ? resAllUser['data'] : (resAllUser['data']?['data'] ?? []);
        _stafList = rawUsers
            .map((item) => PenggunaModel.fromJson(item))
            .where((u) => u.peran?.kode == 'staf' || u.peran?.nama.toLowerCase().contains('staf') == true)
            .toList();
      }

      // 2. Ambil daftar kantor
      final resKantor = await _apiService.get(ApiEndpoints.kantor);
      _kantorList = resKantor['daftar'] as List? ?? (resKantor['data'] as List? ?? []);

      // Default kantor dari pengguna saat ini
      _selectedKantorId = pengguna?.kantor?.id;
      if (_selectedKantorId == null && _kantorList.isNotEmpty) {
        _selectedKantorId = _kantorList.first['id'];
      }

      // 3. Ambil daftar aset untuk dipilih
      final resAset = await _apiService.get('${ApiEndpoints.aset}?per_page=100');
      List rawAset = resAset['data'] is List ? resAset['data'] : (resAset['data']?['data'] ?? []);
      _allAsetList = rawAset.map((item) => AsetModel.fromJson(item)).toList();

      if (mounted) {
        setState(() {
          _isLoadingData = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _loadError = e.toString();
          _isLoadingData = false;
        });
      }
    }
  }

  Future<void> _submitPenugasan() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedStafId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih staf pelaksana tugas terlebih dahulu.'),
          backgroundColor: AppColors.statusDanger,
        ),
      );
      return;
    }

    if (_selectedKantorId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih unit kantor penugasan.'),
          backgroundColor: AppColors.statusDanger,
        ),
      );
      return;
    }

    if (_selectedAsetIds.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih minimal 1 aset yang menjadi target penugasan.'),
          backgroundColor: AppColors.statusDanger,
        ),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final payload = {
        'staf_pelaksana_id': _selectedStafId,
        'kantor_id': _selectedKantorId,
        'jenis_penugasan': _selectedJenis,
        'prioritas': _selectedPrioritas,
        'tanggal_penugasan': _dateFormat.format(_tanggalPenugasan),
        'tenggat_waktu': _dateFormat.format(_tenggatWaktu),
        'instruksi': _instruksiController.text.trim(),
        'aset_ids': _selectedAsetIds.toList(),
      };

      final res = await _apiService.post(ApiEndpoints.penugasan, payload);

      if (mounted) {
        final nomorTugas = res['data']?['nomor_penugasan'] ?? 'Tugas';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Tugas $nomorTugas berhasil didelegasikan kepada staf.'),
            backgroundColor: AppColors.statusSuccess,
          ),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal membuat penugasan: $e'),
            backgroundColor: AppColors.statusDanger,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showAsetPickerDialog() {
    final searchCtrl = TextEditingController();
    List<AsetModel> filtered = List.from(_allAsetList);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => DraggableScrollableSheet(
          initialChildSize: 0.75,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (_, scrollCtrl) => Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade400,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Expanded(
                      child: Text(
                        'Pilih Aset Terkait Tugas',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    TextButton(
                      onPressed: () => Navigator.pop(ctx),
                      child: const Text('Selesai'),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: searchCtrl,
                  decoration: InputDecoration(
                    hintText: 'Cari nama, index, merk...',
                    prefixIcon: const Icon(Icons.search, size: 20),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onChanged: (q) {
                    setModalState(() {
                      filtered = _allAsetList.where((a) {
                        final query = q.toLowerCase();
                        return a.namaAset.toLowerCase().contains(query) ||
                            a.nomorIndex.toLowerCase().contains(query) ||
                            (a.merk ?? '').toLowerCase().contains(query);
                      }).toList();
                    });
                  },
                ),
                const SizedBox(height: 12),
                Expanded(
                  child: filtered.isEmpty
                      ? const Center(child: Text('Tidak ada aset yang sesuai'))
                      : ListView.builder(
                          controller: scrollCtrl,
                          itemCount: filtered.length,
                          itemBuilder: (context, i) {
                            final aset = filtered[i];
                            final isSelected = _selectedAsetIds.contains(aset.id);

                            return CheckboxListTile(
                              value: isSelected,
                              title: Text(
                                aset.namaAset,
                                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              subtitle: Text(
                                '${aset.nomorIndex} • ${aset.kondisi.replaceAll("_", " ").toUpperCase()}',
                                style: const TextStyle(fontSize: 11, color: Colors.grey),
                              ),
                              activeColor: AppColors.orangePrimary,
                              onChanged: (checked) {
                                setModalState(() {
                                  if (checked == true) {
                                    _selectedAsetIds.add(aset.id);
                                  } else {
                                    _selectedAsetIds.remove(aset.id);
                                  }
                                });
                                setState(() {});
                              },
                            );
                          },
                        ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Beri Tugas Kepada Staf'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Muat Ulang Form',
            onPressed: _fetchFormData,
          ),
        ],
      ),
      body: _isLoadingData
          ? const Center(child: CircularProgressIndicator())
          : _loadError != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.statusDanger),
                        const SizedBox(height: 12),
                        Text(_loadError!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: _fetchFormData,
                          icon: const Icon(Icons.refresh),
                          label: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Header Banner
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            gradient: AppColors.navyGradient,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: AppColors.navyDark.withOpacity(0.3),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: const Row(
                            children: [
                              CircleAvatar(
                                radius: 22,
                                backgroundColor: Colors.white24,
                                child: Icon(Icons.assignment_ind_rounded, color: AppColors.orangePrimary, size: 24),
                              ),
                              SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Delegasi Tugas Operasional',
                                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                                    ),
                                    SizedBox(height: 2),
                                    Text(
                                      'Berikan instruksi inspeksi, opname, atau servis aset ke staf teknisi.',
                                      style: TextStyle(color: Colors.white70, fontSize: 11),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 18),

                        // Section 1: Staf & Kantor
                        GlassCard(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('1. Pelaksana & Unit Kerja', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const Divider(height: 20),

                              // Dropdown Staf
                              const Text('Staf Pelaksana Tugas *', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.grey)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<int>(
                                value: _selectedStafId,
                                isExpanded: true,
                                decoration: InputDecoration(
                                  prefixIcon: const Icon(Icons.person_outline, size: 20),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                ),
                                hint: const Text('Pilih staf pelaksana teknis...'),
                                items: _stafList.map((staf) {
                                  return DropdownMenuItem<int>(
                                    value: staf.id,
                                    child: Text(
                                      '${staf.nama} (NIP: ${staf.nip})',
                                      style: const TextStyle(fontSize: 12.5),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  );
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedStafId = val),
                                validator: (val) => val == null ? 'Wajib memilih staf pelaksana' : null,
                              ),
                              const SizedBox(height: 14),

                              // Dropdown Kantor
                              const Text('Unit Kantor Pelaksanaan *', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.grey)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<int>(
                                value: _selectedKantorId,
                                isExpanded: true,
                                decoration: InputDecoration(
                                  prefixIcon: const Icon(Icons.apartment_outlined, size: 20),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                ),
                                hint: const Text('Pilih unit kantor...'),
                                items: _kantorList.map((k) {
                                  return DropdownMenuItem<int>(
                                    value: k['id'],
                                    child: Text(
                                      '${k['kode_kantor']} - ${k['nama_kantor']}',
                                      style: const TextStyle(fontSize: 12.5),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  );
                                }).toList(),
                                onChanged: (val) => setState(() => _selectedKantorId = val),
                                validator: (val) => val == null ? 'Wajib memilih kantor' : null,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Section 2: Jenis & Prioritas
                        GlassCard(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('2. Detail & Prioritas Tugas', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const Divider(height: 20),

                              // Jenis Penugasan
                              const Text('Jenis Penugasan *', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.grey)),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<String>(
                                value: _selectedJenis,
                                isExpanded: true,
                                decoration: InputDecoration(
                                  prefixIcon: Icon(_jenisIcons[_selectedJenis] ?? Icons.assignment_outlined, size: 20),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                ),
                                items: _jenisLabels.entries.map((e) {
                                  return DropdownMenuItem<String>(
                                    value: e.key,
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(_jenisIcons[e.key], size: 18, color: AppColors.orangePrimary),
                                        const SizedBox(width: 10),
                                        Flexible(
                                          child: Text(e.value, style: const TextStyle(fontSize: 12.5), overflow: TextOverflow.ellipsis),
                                        ),
                                      ],
                                    ),
                                  );
                                }).toList(),
                                onChanged: (val) {
                                  if (val != null) setState(() => _selectedJenis = val);
                                },
                              ),
                              const SizedBox(height: 16),

                              // Prioritas Chips
                              const Text('Tingkat Prioritas *', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.grey)),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  _buildPriorityChip('rendah', 'Rendah', AppColors.statusSuccess),
                                  const SizedBox(width: 8),
                                  _buildPriorityChip('sedang', 'Sedang', AppColors.orangePrimary),
                                  const SizedBox(width: 8),
                                  _buildPriorityChip('tinggi', 'Tinggi', Colors.orange.shade800),
                                  const SizedBox(width: 8),
                                  _buildPriorityChip('darurat', 'Darurat', AppColors.statusDanger),
                                ],
                              ),
                              const SizedBox(height: 16),

                              // Tanggal & Tenggat
                              Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text('Tanggal Tugas', style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold)),
                                        const SizedBox(height: 6),
                                        InkWell(
                                          onTap: () async {
                                            final picked = await showDatePicker(
                                              context: context,
                                              initialDate: _tanggalPenugasan,
                                              firstDate: DateTime.now().subtract(const Duration(days: 30)),
                                              lastDate: DateTime.now().add(const Duration(days: 365)),
                                            );
                                            if (picked != null) setState(() => _tanggalPenugasan = picked);
                                          },
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                            decoration: BoxDecoration(
                                              border: Border.all(color: Colors.grey.withOpacity(0.3)),
                                              borderRadius: BorderRadius.circular(10),
                                            ),
                                            child: Row(
                                              children: [
                                                const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                                                const SizedBox(width: 8),
                                                Expanded(
                                                  child: Text(_dateFormat.format(_tanggalPenugasan), style: const TextStyle(fontSize: 12)),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text('Tenggat Waktu *', style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold)),
                                        const SizedBox(height: 6),
                                        InkWell(
                                          onTap: () async {
                                            final picked = await showDatePicker(
                                              context: context,
                                              initialDate: _tenggatWaktu,
                                              firstDate: _tanggalPenugasan,
                                              lastDate: DateTime.now().add(const Duration(days: 365)),
                                            );
                                            if (picked != null) setState(() => _tenggatWaktu = picked);
                                          },
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                            decoration: BoxDecoration(
                                              border: Border.all(color: Colors.grey.withOpacity(0.3)),
                                              borderRadius: BorderRadius.circular(10),
                                            ),
                                            child: Row(
                                              children: [
                                                const Icon(Icons.event_available, size: 16, color: AppColors.orangePrimary),
                                                const SizedBox(width: 8),
                                                Expanded(
                                                  child: Text(_dateFormat.format(_tenggatWaktu), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                                ),
                                              ],
                                            ),
                                          ),
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

                        // Section 3: Aset Target
                        GlassCard(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('3. Target Aset Terkait *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                  TextButton.icon(
                                    onPressed: _showAsetPickerDialog,
                                    icon: const Icon(Icons.add, size: 16),
                                    label: const Text('Pilih Aset'),
                                  ),
                                ],
                              ),
                              const Divider(height: 12),
                              if (_selectedAsetIds.isEmpty)
                                InkWell(
                                  onTap: _showAsetPickerDialog,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
                                    decoration: BoxDecoration(
                                      color: Colors.black.withOpacity(0.02),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: Colors.grey.withOpacity(0.3), style: BorderStyle.solid),
                                    ),
                                    child: const Center(
                                      child: Column(
                                        children: [
                                          Icon(Icons.touch_app_outlined, color: AppColors.orangePrimary, size: 28),
                                          SizedBox(height: 6),
                                          Text('Ketuk di sini untuk memilih aset yang ditugaskan', style: TextStyle(fontSize: 12, color: Colors.grey)),
                                        ],
                                      ),
                                    ),
                                  ),
                                )
                              else
                                Column(
                                  children: _selectedAsetIds.map((id) {
                                    final item = _allAsetList.firstWhere(
                                      (a) => a.id == id,
                                      orElse: () => AsetModel(
                                        id: id,
                                        nomorIndex: 'Aset #$id',
                                        namaAset: 'Aset #$id',
                                        jenisKepemilikan: 'milik_sendiri',
                                        kondisi: 'baik',
                                        statusOperasional: 'aktif',
                                        nilaiPerolehan: 0,
                                        nilaiBukuSaatIni: 0,
                                        akumulasiPenyusutan: 0,
                                        tanggalPerolehan: '',
                                        statusInventaris: 'lengkap',
                                      ),
                                    );

                                    return Container(
                                      margin: const EdgeInsets.only(bottom: 8),
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                      decoration: BoxDecoration(
                                        color: isDark ? const Color(0xFF1E293B) : Colors.grey.shade100,
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: Row(
                                        children: [
                                          const Icon(Icons.devices_other_outlined, size: 18, color: AppColors.orangePrimary),
                                          const SizedBox(width: 10),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(item.namaAset, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12), maxLines: 1, overflow: TextOverflow.ellipsis),
                                                Text(item.nomorIndex, style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                              ],
                                            ),
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.close, size: 16, color: Colors.grey),
                                            onPressed: () {
                                              setState(() => _selectedAsetIds.remove(id));
                                            },
                                          ),
                                        ],
                                      ),
                                    );
                                  }).toList(),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Section 4: Instruksi Kerja
                        GlassCard(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('4. Instruksi Kerja Spesifik *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const Divider(height: 20),
                              TextFormField(
                                controller: _instruksiController,
                                maxLines: 4,
                                decoration: InputDecoration(
                                  hintText: 'Tuliskan rincian instruksi operasional untuk staf teknisi, langkah kerja, atau target pengecekan...',
                                  hintStyle: const TextStyle(fontSize: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.all(14),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) {
                                    return 'Instruksi penugasan wajib diisi';
                                  }
                                  if (val.trim().length < 5) {
                                    return 'Instruksi minimal 5 karakter';
                                  }
                                  return null;
                                },
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Submit Button
                        ElevatedButton.icon(
                          onPressed: _isSubmitting ? null : _submitPenugasan,
                          icon: _isSubmitting
                              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Icon(Icons.send_rounded, color: Colors.white),
                          label: Text(
                            _isSubmitting ? 'Mendelegasikan Tugas...' : 'Kirim & Delegasikan Penugasan',
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.orangePrimary,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            elevation: 4,
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildPriorityChip(String value, String label, Color color) {
    final isSelected = _selectedPrioritas == value;

    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _selectedPrioritas = value),
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? color : color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: color, width: isSelected ? 1.5 : 0.8),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              color: isSelected ? Colors.white : color,
            ),
          ),
        ),
      ),
    );
  }
}
