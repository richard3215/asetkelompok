import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/penugasan_model.dart';
import 'penugasan_detail_screen.dart';
import 'penugasan_form_screen.dart';

class PenugasanListScreen extends StatefulWidget {
  const PenugasanListScreen({super.key});

  @override
  State<PenugasanListScreen> createState() => _PenugasanListScreenState();
}

class _PenugasanListScreenState extends State<PenugasanListScreen> {
  final ApiService _apiService = ApiService();

  List<PenugasanModel> _allTasks = [];
  List<PenugasanModel> _filteredTasks = [];
  bool _isLoading = true;
  String? _error;
  String _selectedStatus = 'semua';

  @override
  void initState() {
    super.initState();
    _loadTasks();
  }

  Future<void> _loadTasks() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.penugasan);
      final List raw = res['data'] is List ? res['data'] : (res['data']?['data'] ?? []);
      setState(() {
        _allTasks = raw.map((item) => PenugasanModel.fromJson(item)).toList();
        _filterTasks();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  void _filterTasks() {
    setState(() {
      if (_selectedStatus == 'semua') {
        _filteredTasks = List.from(_allTasks);
      } else {
        _filteredTasks = _allTasks.where((t) => t.statusPenugasan == _selectedStatus).toList();
      }
    });
  }

  void _openCreateTaskScreen() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const PenugasanFormScreen()),
    ).then((val) {
      if (val == true) {
        _loadTasks();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final auth = context.watch<AuthProvider>();
    final canCreateTask = auth.pengguna?.isManajer == true || auth.pengguna?.isAdmin == true;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Penugasan Staf'),
        actions: [
          if (canCreateTask)
            IconButton(
              icon: const Icon(Icons.add_task_rounded, color: AppColors.orangePrimary),
              tooltip: 'Beri Tugas Baru',
              onPressed: _openCreateTaskScreen,
            ),
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Segarkan',
            onPressed: _loadTasks,
          ),
        ],
      ),
      floatingActionButton: canCreateTask
          ? FloatingActionButton.extended(
              onPressed: _openCreateTaskScreen,
              backgroundColor: AppColors.orangePrimary,
              icon: const Icon(Icons.add_task_rounded, color: Colors.white),
              label: const Text(
                'Beri Tugas Baru',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
              ),
            )
          : null,
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
                _buildStatusChip('Diberikan', 'diberikan'),
                const SizedBox(width: 6),
                _buildStatusChip('Dikerjakan', 'dikerjakan'),
                const SizedBox(width: 6),
                _buildStatusChip('Menunggu Validasi', 'menunggu_validasi'),
                const SizedBox(width: 6),
                _buildStatusChip('Selesai', 'selesai'),
                const SizedBox(width: 6),
                _buildStatusChip('Perlu Revisi', 'perlu_revisi'),
              ],
            ),
          ),

          // Task List
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
                            ElevatedButton(
                              onPressed: _loadTasks,
                              child: const Text('Coba Lagi'),
                            ),
                          ],
                        ),
                      )
                    : _filteredTasks.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.assignment_turned_in_outlined, size: 54, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                Text('Tidak ada penugasan pada filter ini', style: TextStyle(color: Colors.grey.shade600)),
                                if (canCreateTask) ...[
                                  const SizedBox(height: 14),
                                  ElevatedButton.icon(
                                    onPressed: _openCreateTaskScreen,
                                    icon: const Icon(Icons.add_task_rounded, color: Colors.white),
                                    label: const Text('Tugaskan Staf Sekarang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.orangePrimary),
                                  ),
                                ],
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadTasks,
                            child: ListView.builder(
                              padding: const EdgeInsets.all(16),
                              itemCount: _filteredTasks.length,
                              itemBuilder: (context, index) {
                                final task = _filteredTasks[index];
                                return _buildTaskCard(task, isDark);
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
            _filterTasks();
          });
        }
      },
    );
  }

  Widget _buildTaskCard(PenugasanModel task, bool isDark) {
    Color priorityColor = Colors.grey;
    if (task.prioritas == 'tinggi') priorityColor = AppColors.statusDanger;
    if (task.prioritas == 'sedang') priorityColor = AppColors.orangePrimary;
    if (task.prioritas == 'rendah') priorityColor = AppColors.statusSuccess;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => PenugasanDetailScreen(task: task),
          ),
        ).then((_) => _loadTasks());
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  task.nomorPenugasan,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.orangePrimary),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              CustomBadge(text: task.statusPenugasan, type: BadgeType.status),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            task.instruksi,
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 6,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: priorityColor.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  'PRIORITAS ${task.prioritas.toUpperCase()}',
                  style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: priorityColor),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.navyPrimary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  task.jenisPenugasan.replaceAll('_', ' ').toUpperCase(),
                  style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.navyPrimary),
                ),
              ),
            ],
          ),
          const Divider(height: 18),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Row(
                  children: [
                    const Icon(Icons.person_outline, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        'Staf: ${task.namaStaf ?? "-"}',
                        style: const TextStyle(fontSize: 11),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Flexible(
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.calendar_today_outlined, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Flexible(
                      child: Text(
                        'Tenggat: ${task.tenggatWaktu}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
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
