import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/pengguna_model.dart';

import '../notifikasi/notifikasi_screen.dart';
import '../penugasan/penugasan_form_screen.dart';
import '../profil/profil_screen.dart';

class DashboardScreen extends StatefulWidget {
  final Function(int)? onNavigateTab;

  const DashboardScreen({super.key, this.onNavigateTab});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _apiService = ApiService();
  final NumberFormat _currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  bool _isLoading = true;
  Map<String, dynamic>? _dashboardData;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _apiService.get(ApiEndpoints.dashboard);
      if (mounted) {
        setState(() {
          _dashboardData = res['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final pengguna = auth.pengguna;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'SI-ASET',
              style: TextStyle(
                fontSize: 14,
                letterSpacing: 1.2,
                fontWeight: FontWeight.bold,
                color: AppColors.orangePrimary,
              ),
            ),
            ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 190),
              child: Text(
                pengguna?.kantor?.nama ?? 'Dashboard Operasional',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.normal,
                  color: isDark ? Colors.white70 : Colors.blueGrey.shade700,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            tooltip: 'Notifikasi',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const NotifikasiScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.account_circle_outlined),
            tooltip: 'Profil Saya',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const ProfilScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadDashboard,
            tooltip: 'Segarkan Data',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.statusDanger),
                        const SizedBox(height: 12),
                        Text(_error!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: _loadDashboard,
                          icon: const Icon(Icons.refresh),
                          label: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadDashboard,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Welcome Banner with Glassmorphism
                        _buildUserGreetingBanner(pengguna, isDark),
                        const SizedBox(height: 20),

                        // Quick Action Buttons Grid
                        _buildQuickActionGrid(),
                        const SizedBox(height: 24),

                        // Role-specific Section
                        if (pengguna?.isAdmin == true)
                          _buildAdminDashboard()
                        else if (pengguna?.isKepalaKantor == true)
                          _buildKepalaKantorDashboard()
                        else if (pengguna?.isManajer == true)
                          _buildManajerDashboard()
                        else
                          _buildStafDashboard(),

                        const SizedBox(height: 30),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildUserGreetingBanner(PenggunaModel? pengguna, bool isDark) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: AppColors.navyGradient,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppColors.navyDark.withOpacity(0.3),
            blurRadius: 16,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 26,
            backgroundColor: AppColors.orangePrimary.withOpacity(0.2),
            child: const Icon(Icons.person, color: Colors.white, size: 30),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Halo, ${pengguna?.nama ?? "Pengguna"}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.orangePrimary,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        (pengguna?.peran?.nama ?? 'STAF').toUpperCase(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'NIP: ${pengguna?.nip ?? "-"}',
                        style: const TextStyle(color: Colors.white70, fontSize: 11),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionGrid() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'AKSI CEPAT OPERASIONAL',
          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildActionTile(
                title: 'Scan QR / Barcode',
                icon: Icons.qr_code_scanner_rounded,
                color: AppColors.orangePrimary,
                onTap: () => widget.onNavigateTab?.call(2), // Tab scan
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildActionTile(
                title: 'Daftar Aset',
                icon: Icons.inventory_2_outlined,
                color: AppColors.navyPrimary,
                onTap: () => widget.onNavigateTab?.call(1), // Tab aset
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildActionTile(
                title: 'Penugasan Staf',
                icon: Icons.task_alt_rounded,
                color: const Color(0xFF2A9D8F),
                onTap: () => widget.onNavigateTab?.call(3), // Tab tugas/pengadaan
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildActionTile(
                title: 'Laporan & PDF',
                icon: Icons.picture_as_pdf_outlined,
                color: const Color(0xFF7209B7),
                onTap: () => widget.onNavigateTab?.call(4), // Tab laporan
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionTile({
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return GlassCard(
      onTap: onTap,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              title,
              style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  // --- ADMIN VIEW ---
  Widget _buildAdminDashboard() {
    final stats = _dashboardData?['statistik'] ?? {};
    final kondisi = _dashboardData?['distribusi_kondisi'] ?? {};
    final aktivitas = _dashboardData?['aktivitas_terkini'] as List? ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'STATISTIK SISTEM TERPUSAT',
          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'Total Unit Aset',
                value: '${stats['total_aset'] ?? 0} Unit',
                subtitle: '${stats['total_aset_milik'] ?? 0} Milik | ${stats['total_aset_sewa'] ?? 0} Sewa',
                icon: Icons.domain_verification_rounded,
                color: AppColors.navyPrimary,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMetricCard(
                title: 'Nilai Buku Saat Ini',
                value: _currencyFormatter.format(stats['total_nilai_buku'] ?? 0),
                subtitle: 'Dari Perolehan ${_currencyFormatter.format(stats['total_nilai_perolehan'] ?? 0)}',
                icon: Icons.savings_outlined,
                color: AppColors.statusSuccess,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Kondisi Breakdown
        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Distribusi Kondisi Fisik Aset', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  Expanded(child: _buildConditionCounter('Sangat Baik', kondisi['sangat_baik'] ?? 0, AppColors.statusSuccess)),
                  Expanded(child: _buildConditionCounter('Baik', kondisi['baik'] ?? 0, Colors.blue)),
                  Expanded(child: _buildConditionCounter('Rusak Ringan', kondisi['rusak_ringan'] ?? 0, AppColors.statusWarning)),
                  Expanded(child: _buildConditionCounter('Rusak Berat', kondisi['rusak_berat'] ?? 0, AppColors.statusDanger)),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Log Aktivitas Terkini
        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Audit Log Aktivitas Terkini', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  Icon(Icons.history, size: 16, color: Colors.grey),
                ],
              ),
              const Divider(height: 20),
              if (aktivitas.isEmpty)
                const Text('Belum ada riwayat aktivitas.', style: TextStyle(fontSize: 12, color: Colors.grey))
              else
                ...aktivitas.map((log) => Padding(
                      padding: const EdgeInsets.only(bottom: 10.0),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            margin: const EdgeInsets.only(top: 2),
                            width: 8,
                            height: 8,
                            decoration: const BoxDecoration(
                              shape: BoxShape.circle,
                              color: AppColors.orangePrimary,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(log['deskripsi'] ?? '', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                                Text(
                                  '${log['nama_pengguna']} • ${log['waktu']}',
                                  style: const TextStyle(fontSize: 10.5, color: Colors.grey),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    )),
            ],
          ),
        ),
      ],
    );
  }

  // --- KEPALA KANTOR VIEW ---
  Widget _buildKepalaKantorDashboard() {
    final stats = _dashboardData?['statistik'] ?? {};
    final pengadaanList = _dashboardData?['pengadaan_butuh_tindakan'] as List? ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'PENGAWASAN & PERSETUJUAN KEPALA KANTOR',
          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'Pengadaan Menunggu',
                value: '${stats['pengadaan_menunggu_persetujuan'] ?? 0}',
                subtitle: 'Memerlukan Persetujuan',
                icon: Icons.approval_rounded,
                color: AppColors.orangePrimary,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMetricCard(
                title: 'Total Aset Unit',
                value: '${stats['total_aset_kantor'] ?? 0} Unit',
                subtitle: 'Nilai Buku ${_currencyFormatter.format(stats['nilai_aset_kantor'] ?? 0)}',
                icon: Icons.apartment_rounded,
                color: AppColors.navyPrimary,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Pengadaan Memerlukan Tindakan
        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Expanded(
                    child: Text(
                      'Pengadaan Menunggu Persetujuan',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
                  CustomBadge(
                    text: '${pengadaanList.length} MENUNGGU',
                    backgroundColor: AppColors.statusWarning.withValues(alpha: 0.2),
                    textColor: AppColors.statusWarning,
                  ),
                ],
              ),
              const Divider(height: 20),
              if (pengadaanList.isEmpty)
                const Text('Semua pengajuan telah diproses.', style: TextStyle(fontSize: 12, color: Colors.grey))
              else
                ...pengadaanList.map((prc) => Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.02),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.grey.withValues(alpha: 0.2)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  prc['nomor_pengadaan'] ?? '',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                _currencyFormatter.format(double.tryParse(prc['estimasi_total_biaya']?.toString() ?? '0') ?? 0),
                                style: const TextStyle(color: AppColors.orangeDark, fontWeight: FontWeight.bold, fontSize: 12),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(prc['judul_pengadaan'] ?? '', style: const TextStyle(fontSize: 12)),
                          const SizedBox(height: 4),
                          Text(
                            'Diajukan oleh: ${prc['pengaju']?['nama'] ?? '-'} (${prc['tanggal_pengajuan'] ?? '-'})',
                            style: const TextStyle(fontSize: 10.5, color: Colors.grey),
                          ),
                        ],
                      ),
                    )),
            ],
          ),
        ),
      ],
    );
  }

  // --- MANAJER VIEW ---
  Widget _buildManajerDashboard() {
    final stats = _dashboardData?['statistik'] ?? {};
    final tugasValidasi = _dashboardData?['tugas_validasi'] as List? ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Expanded(
              child: Text(
                'MANAJEMEN OPERASIONAL & PENUGASAN',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8),
                overflow: TextOverflow.ellipsis,
              ),
            ),
            TextButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => const PenugasanFormScreen()),
                ).then((_) => _loadDashboard());
              },
              icon: const Icon(Icons.add_task_rounded, size: 16, color: AppColors.orangePrimary),
              label: const Text(
                'Beri Tugas Staf',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.orangePrimary),
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'Tugas Validasi',
                value: '${stats['tugas_menunggu_validasi'] ?? 0}',
                subtitle: 'Menunggu Disahkan',
                icon: Icons.checklist_rtl_rounded,
                color: AppColors.orangePrimary,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMetricCard(
                title: 'BAST Pending',
                value: '${stats['bast_menunggu_persetujuan'] ?? 0}',
                subtitle: 'Serah Terima Baru',
                icon: Icons.description_outlined,
                color: const Color(0xFF2A9D8F),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),

        // CTA Banner Delegasi Tugas Staf
        GlassCard(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const PenugasanFormScreen()),
            ).then((_) => _loadDashboard());
          },
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.orangePrimary.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.assignment_ind_rounded, color: AppColors.orangePrimary, size: 24),
              ),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Beri Penugasan Kepada Staf',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'Delegasikan inspeksi fisik, servis berkala, atau opname aset.',
                      style: TextStyle(fontSize: 11, color: Colors.grey),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.orangePrimary),
            ],
          ),
        ),
        const SizedBox(height: 16),

        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Tugas Selesai Staf Menunggu Verifikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const Divider(height: 20),
              if (tugasValidasi.isEmpty)
                const Text('Tidak ada tugas menunggu validasi.', style: TextStyle(fontSize: 12, color: Colors.grey))
              else
                ...tugasValidasi.map((t) => Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.02),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  t['nomor_penugasan'] ?? '',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'Pelaksana: ${t['staf_pelaksana']?['nama'] ?? '-'}',
                                  style: const TextStyle(fontSize: 11),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          CustomBadge.status(t['status_penugasan'] ?? 'menunggu_validasi'),
                        ],
                      ),
                    )),
            ],
          ),
        ),
      ],
    );
  }

  // --- STAF VIEW ---
  Widget _buildStafDashboard() {
    final stats = _dashboardData?['statistik'] ?? {};
    final tugasAktif = _dashboardData?['daftar_tugas_aktif'] as List? ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'TUGAS & PEKERJAAN TEKNISI',
          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'Tugas Aktif',
                value: '${stats['tugas_aktif'] ?? 0}',
                subtitle: 'Perlu Dikerjakan',
                icon: Icons.pending_actions_rounded,
                color: AppColors.orangePrimary,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMetricCard(
                title: 'Tugas Selesai',
                value: '${stats['tugas_selesai'] ?? 0}',
                subtitle: 'Telah Diverifikasi',
                icon: Icons.task_alt_rounded,
                color: AppColors.statusSuccess,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Daftar Pekerjaan Ditugaskan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const Divider(height: 20),
              if (tugasAktif.isEmpty)
                const Text('Tidak ada tugas aktif saat ini.', style: TextStyle(fontSize: 12, color: Colors.grey))
              else
                ...tugasAktif.map((t) => Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.02),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  t['nomor_penugasan'] ?? '',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(width: 8),
                              CustomBadge.status(t['status_penugasan'] ?? 'diberikan'),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(t['instruksi'] ?? '', style: const TextStyle(fontSize: 11.5)),
                          const SizedBox(height: 4),
                          Text('Tenggat: ${t['tenggat_waktu'] ?? '-'}', style: const TextStyle(fontSize: 10.5, color: Colors.grey)),
                        ],
                      ),
                    )),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
  }) {
    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.grey),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Icon(icon, color: color, size: 20),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color),
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(fontSize: 10, color: Colors.grey),
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildConditionCounter(String label, dynamic count, Color color) {
    return Column(
      children: [
        Text(
          '$count',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(fontSize: 10, color: Colors.grey),
        ),
      ],
    );
  }
}
