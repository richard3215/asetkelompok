import 'package:flutter/material.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/notifikasi_model.dart';

class NotifikasiScreen extends StatefulWidget {
  const NotifikasiScreen({super.key});

  @override
  State<NotifikasiScreen> createState() => _NotifikasiScreenState();
}

class _NotifikasiScreenState extends State<NotifikasiScreen> {
  final ApiService _apiService = ApiService();

  List<NotifikasiModel> _notifications = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.notifikasi);
      final List raw = res['data'] is List ? res['data'] : (res['data']?['data'] ?? []);
      setState(() {
        _notifications = raw.map((item) => NotifikasiModel.fromJson(item)).toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _markAllRead() async {
    try {
      await _apiService.post('${ApiEndpoints.notifikasi}/baca-semua');
      _loadNotifications();
    } catch (_) {}
  }

  Future<void> _markAsRead(int id) async {
    try {
      await _apiService.put('${ApiEndpoints.notifikasi}/$id/baca');
      _loadNotifications();
    } catch (_) {}
  }

  IconData _getIconForType(String type) {
    switch (type) {
      case 'pemeliharaan':
        return Icons.build_circle_outlined;
      case 'penugasan':
        return Icons.task_alt_outlined;
      case 'persetujuan':
        return Icons.rule_folder_outlined;
      case 'kontrak_sewa':
        return Icons.assignment_late_outlined;
      case 'peremajaan':
        return Icons.autorenew_rounded;
      default:
        return Icons.notifications_none_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifikasi & Peringatan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.done_all_rounded),
            tooltip: 'Tandai Semua Dibaca',
            onPressed: _notifications.isEmpty ? null : _markAllRead,
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadNotifications,
          ),
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
                      ElevatedButton(onPressed: _loadNotifications, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : _notifications.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.notifications_off_outlined, size: 54, color: Colors.grey.shade400),
                          const SizedBox(height: 12),
                          Text('Tidak ada notifikasi aktif', style: TextStyle(color: Colors.grey.shade600)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadNotifications,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _notifications.length,
                        itemBuilder: (context, index) {
                          final notif = _notifications[index];
                          final icon = _getIconForType(notif.tipe);

                          return GlassCard(
                            margin: const EdgeInsets.only(bottom: 10),
                            onTap: () {
                              if (!notif.sudahDibaca) {
                                _markAsRead(notif.id);
                              }
                            },
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  width: 40,
                                  height: 40,
                                  decoration: BoxDecoration(
                                    color: notif.sudahDibaca
                                        ? Colors.grey.withOpacity(0.15)
                                        : AppColors.orangePrimary.withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Icon(
                                    icon,
                                    color: notif.sudahDibaca ? Colors.grey : AppColors.orangePrimary,
                                    size: 22,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Expanded(
                                            child: Text(
                                              notif.judul,
                                              style: TextStyle(
                                                fontSize: 13,
                                                fontWeight: notif.sudahDibaca ? FontWeight.w500 : FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                          if (!notif.sudahDibaca)
                                            Container(
                                              width: 8,
                                              height: 8,
                                              decoration: const BoxDecoration(
                                                color: AppColors.orangePrimary,
                                                shape: BoxShape.circle,
                                              ),
                                            ),
                                        ],
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        notif.pesan,
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: isDark ? Colors.white70 : Colors.black87,
                                        ),
                                      ),
                                      if (notif.createdAt != null) ...[
                                        const SizedBox(height: 6),
                                        Text(
                                          notif.createdAt!,
                                          style: const TextStyle(fontSize: 10, color: Colors.grey),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}
