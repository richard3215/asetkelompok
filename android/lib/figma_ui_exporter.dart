import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'core/constants/colors.dart';
import 'core/providers/auth_provider.dart';
import 'core/providers/theme_provider.dart';
import 'core/theme/app_theme.dart';
import 'core/widgets/custom_badge.dart';
import 'core/widgets/glass_card.dart';
import 'models/pengguna_model.dart';
import 'screens/aset/aset_list_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/bast/bast_list_screen.dart';
import 'screens/laporan/laporan_screen.dart';
import 'screens/main_navigation_screen.dart';
import 'screens/notifikasi/notifikasi_screen.dart';
import 'screens/operasional/operasional_hub_screen.dart';
import 'screens/pengadaan/pengadaan_list_screen.dart';
import 'screens/penugasan/penugasan_list_screen.dart';
import 'screens/profil/profil_screen.dart';
import 'screens/scan/scan_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider<AuthProvider>(create: (_) => MockAuthProviderForFigma()),
      ],
      child: const FigmaExporterApp(),
    ),
  );
}

/// Mock AuthProvider untuk UI preview & Figma Export tanpa butuh API Backend
class MockAuthProviderForFigma extends AuthProvider {
  MockAuthProviderForFigma() : super();

  @override
  bool get isLoading => false;

  @override
  bool get isAuthenticated => true;

  @override
  PenggunaModel? get pengguna => PenggunaModel(
        id: 1,
        nip: '199208152020011001',
        nama: 'Kelompok 1',
        email: 'kelompok1@siaset.go.id',
        nomorTelepon: '081234567890',
        peran: PeranModel(id: 1, kode: 'PENGELOLA', nama: 'Pengelola Aset'),
        kantor: KantorModel(id: 101, kode: 'KP-JKT', nama: 'Kantor Pusat Jakarta', jenis: 'pusat'),
        divisi: DivisiModel(id: 5, kode: 'TI', nama: 'Teknologi Informasi & Infrastruktur'),
        hakAkses: ['aset.lihat', 'aset.kelola', 'penugasan.kelola', 'bast.kelola'],
      );
}

class FigmaExporterApp extends StatelessWidget {
  const FigmaExporterApp({super.key});

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();

    return MaterialApp(
      title: 'SI-ASET - Figma UI Exporter Showcase',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: themeProvider.themeMode,
      home: const FigmaGalleryHomeScreen(),
    );
  }
}

class FigmaGalleryHomeScreen extends StatefulWidget {
  const FigmaGalleryHomeScreen({super.key});

  @override
  State<FigmaGalleryHomeScreen> createState() => _FigmaGalleryHomeScreenState();
}

class _FigmaGalleryHomeScreenState extends State<FigmaGalleryHomeScreen> {
  int _selectedScreenIndex = 0;

  final List<Map<String, dynamic>> _screens = [
    {
      'title': 'Design System & Components',
      'icon': Icons.palette_outlined,
      'widget': const DesignSystemShowcase(),
    },
    {
      'title': 'Login Screen',
      'icon': Icons.lock_outline,
      'widget': const LoginScreen(),
    },
    {
      'title': 'Main Navigation & Dashboard',
      'icon': Icons.dashboard_outlined,
      'widget': const MainNavigationScreen(),
    },
    {
      'title': 'Daftar Aset',
      'icon': Icons.inventory_2_outlined,
      'widget': const AsetListScreen(),
    },
    {
      'title': 'Pengadaan Aset',
      'icon': Icons.shopping_cart_outlined,
      'widget': const PengadaanListScreen(),
    },
    {
      'title': 'Penugasan & Perawatan',
      'icon': Icons.assignment_outlined,
      'widget': const PenugasanListScreen(),
    },
    {
      'title': 'Berita Acara (BAST)',
      'icon': Icons.verified_outlined,
      'widget': const BastListScreen(),
    },
    {
      'title': 'Operasional Hub',
      'icon': Icons.build_circle_outlined,
      'widget': const OperasionalHubScreen(),
    },
    {
      'title': 'Laporan & Analitik',
      'icon': Icons.assessment_outlined,
      'widget': const LaporanScreen(),
    },
    {
      'title': 'Notifikasi Center',
      'icon': Icons.notifications_outlined,
      'widget': const NotifikasiScreen(),
    },
    {
      'title': 'Scan QR Code',
      'icon': Icons.qr_code_scanner_outlined,
      'widget': const ScanScreen(),
    },
    {
      'title': 'Profil Pengguna',
      'icon': Icons.person_outline,
      'widget': const ProfilScreen(),
    },
  ];

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.read<ThemeProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: AppColors.orangePrimary,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.art_track, color: Colors.white, size: 20),
            ),
            const SizedBox(width: 10),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'SI-ASET Figma UI Exporter',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                Text(
                  'Pratinjau Komponen & Halaman Khusus UI',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.normal),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(isDark ? Icons.light_mode : Icons.dark_mode),
            tooltip: 'Toggle Light/Dark Theme',
            onPressed: () => themeProvider.toggleTheme(),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Row(
        children: [
          // Sidebar menu for desktop/web preview
          Container(
            width: 280,
            decoration: BoxDecoration(
              color: isDark ? AppColors.cardDark : Colors.white,
              border: Border(
                right: BorderSide(
                  color: isDark ? Colors.white12 : Colors.black.withValues(alpha: 0.08),
                ),
              ),
            ),
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 12),
              children: [
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Text(
                    'DAFTAR HALAMAN UI',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 1.2,
                      color: Colors.grey,
                    ),
                  ),
                ),
                ...List.generate(_screens.length, (index) {
                  final item = _screens[index];
                  final isSelected = index == _selectedScreenIndex;
                  return ListTile(
                    dense: true,
                    selected: isSelected,
                    selectedTileColor: AppColors.orangePrimary.withValues(alpha: 0.12),
                    leading: Icon(
                      item['icon'] as IconData,
                      color: isSelected ? AppColors.orangePrimary : (isDark ? Colors.white70 : AppColors.navyPrimary),
                    ),
                    title: Text(
                      item['title'] as String,
                      style: TextStyle(
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                        color: isSelected ? AppColors.orangePrimary : (isDark ? Colors.white : AppColors.navyDarkest),
                      ),
                    ),
                    onTap: () {
                      setState(() {
                        _selectedScreenIndex = index;
                      });
                    },
                  );
                }),
              ],
            ),
          ),

          // Main Screen Frame
          Expanded(
            child: Container(
              color: isDark ? AppColors.bgDark : AppColors.bgLight,
              child: _screens[_selectedScreenIndex]['widget'] as Widget,
            ),
          ),
        ],
      ),
    );
  }
}

/// Showcase Komponen UI & Token Warna untuk Figma Export
class DesignSystemShowcase extends StatelessWidget {
  const DesignSystemShowcase({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'SI-ASET UI Design Tokens & Components',
            style: Theme.of(context).textTheme.headlineLarge,
          ),
          const SizedBox(height: 8),
          Text(
            'Panduan visual token warna, tipografi, badge status, dan kartu UI untuk Figma Design System.',
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
          ),
          const SizedBox(height: 24),

          // Color Palette Cards
          const Text(
            '1. Palet Warna Utama (Color Tokens)',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              _buildColorBox('Navy Darkest', AppColors.navyDarkest, '#05162A'),
              _buildColorBox('Navy Dark', AppColors.navyDark, '#0B2545'),
              _buildColorBox('Navy Primary', AppColors.navyPrimary, '#13315C'),
              _buildColorBox('Navy Light', AppColors.navyLight, '#134074'),
              _buildColorBox('Navy Lighter', AppColors.navyLighter, '#1D5A9C'),
              _buildColorBox('Orange Primary', AppColors.orangePrimary, '#FF7A00'),
              _buildColorBox('Orange Light', AppColors.orangeLight, '#FF9429'),
              _buildColorBox('Orange Dark', AppColors.orangeDark, '#E05E00'),
              _buildColorBox('Orange Accent', AppColors.orangeAccent, '#FFB703'),
              _buildColorBox('Status Success', AppColors.statusSuccess, '#00A86B'),
              _buildColorBox('Status Warning', AppColors.statusWarning, '#FF9F1C'),
              _buildColorBox('Status Danger', AppColors.statusDanger, '#E63946'),
              _buildColorBox('Status Info', AppColors.statusInfo, '#2A9D8F'),
            ],
          ),

          const SizedBox(height: 32),
          const Text(
            '2. Status Badges Component',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              CustomBadge.status('aktif'),
              CustomBadge.status('menunggu_persetujuan'),
              CustomBadge.status('ditolak'),
              CustomBadge.kondisi('sangat_baik'),
              CustomBadge.kepemilikan('milik_sendiri'),
            ],
          ),

          const SizedBox(height: 32),
          const Text(
            '3. Glassmorphic Card & Elevated Cards',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: AppColors.orangePrimary.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.devices, color: AppColors.orangePrimary),
                          ),
                          const SizedBox(width: 12),
                          const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Laptop MacBook Pro M2', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                              Text('SN: MBP-2024-9901', style: TextStyle(fontSize: 12, color: Colors.grey)),
                            ],
                          ),
                          const Spacer(),
                          CustomBadge.kondisi('baik'),
                        ],
                      ),
                      const Divider(height: 24),
                      const Text('Kategori: Barang Elektronik'),
                      const Text('Nilai Perolehan: Rp 24.500.000'),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const CircleAvatar(
                              backgroundColor: AppColors.navyPrimary,
                              child: Icon(Icons.person, color: Colors.white),
                            ),
                            const SizedBox(width: 12),
                            const Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Kelompok 1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                                Text('Pengelola Aset Utama', style: TextStyle(fontSize: 12, color: Colors.grey)),
                              ],
                            ),
                            const Spacer(),
                            CustomBadge.status('aktif'),
                          ],
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () {},
                          icon: const Icon(Icons.edit, size: 18),
                          label: const Text('Edit Profil'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 32),
          const Text(
            '4. Typography System (Plus Jakarta Sans)',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Headline Large (28px, Bold)', style: Theme.of(context).textTheme.headlineLarge),
                  const SizedBox(height: 8),
                  Text('Headline Medium (22px, Bold)', style: Theme.of(context).textTheme.headlineMedium),
                  const SizedBox(height: 8),
                  Text('Title Large (18px, Bold)', style: Theme.of(context).textTheme.titleLarge),
                  const SizedBox(height: 8),
                  Text('Title Medium (15px, SemiBold)', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  Text('Body Large (14px, Medium)', style: Theme.of(context).textTheme.bodyLarge),
                  const SizedBox(height: 8),
                  Text('Body Medium (13px, Regular)', style: Theme.of(context).textTheme.bodyMedium),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  static Widget _buildColorBox(String title, Color color, String hex) {
    return Container(
      width: 150,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 50,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(8),
            ),
          ),
          const SizedBox(height: 8),
          Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.black87)),
          Text(hex, style: const TextStyle(fontSize: 11, color: Colors.grey)),
        ],
      ),
    );
  }
}
