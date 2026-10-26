import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/providers/theme_provider.dart';
import '../../core/widgets/glass_card.dart';
import '../auth/login_screen.dart';

class ProfilScreen extends StatelessWidget {
  const ProfilScreen({super.key});

  void _showEditServerDialog(BuildContext context) {
    final controller = TextEditingController(text: ApiEndpoints.baseUrl);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Konfigurasi Server API', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Alamat endpoint Laravel API:', style: TextStyle(fontSize: 12)),
            const SizedBox(height: 8),
            TextField(
              controller: controller,
              decoration: const InputDecoration(
                hintText: 'http://10.0.2.2:8000/api/v1',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Gunakan 10.0.2.2 untuk Android Emulator, atau IP lokal PC (misal: 192.168.1.5) untuk perangkat fisik.',
              style: TextStyle(fontSize: 11, color: Colors.grey),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.orangePrimary),
            onPressed: () {
              ApiEndpoints.setBaseUrl(controller.text.trim());
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Alamat server diperbarui')),
              );
            },
            child: const Text('Simpan', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _confirmLogout(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Keluar dari Akun?', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: const Text('Token sesi Anda akan dicabut dan Anda harus memasukkan kata sandi kembali untuk masuk.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.statusDanger),
            onPressed: () async {
              Navigator.pop(ctx);
              final auth = context.read<AuthProvider>();
              await auth.logout();
              if (context.mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                  (route) => false,
                );
              }
            },
            child: const Text('Ya, Keluar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final themeProvider = context.watch<ThemeProvider>();
    final pengguna = auth.pengguna;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil Pengguna'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // User Header Profile Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: AppColors.navyGradient,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(color: AppColors.navyDark.withOpacity(0.3), blurRadius: 14, offset: const Offset(0, 6)),
                ],
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 36,
                    backgroundColor: AppColors.orangePrimary.withOpacity(0.2),
                    child: const Icon(Icons.person, size: 44, color: Colors.white),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    pengguna?.nama ?? 'Nama Pengguna',
                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.orangePrimary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      (pengguna?.peran?.nama ?? 'STAF').toUpperCase(),
                      style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    pengguna?.email ?? '-',
                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Organizational Position
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('PENEMPATAN ORGANISASI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8)),
                  const SizedBox(height: 10),
                  _buildProfileRow(Icons.badge_outlined, 'NIP', pengguna?.nip ?? '-'),
                  _buildProfileRow(Icons.account_balance_outlined, 'Kantor / Cabang', pengguna?.kantor?.nama ?? '-'),
                  _buildProfileRow(Icons.apartment_outlined, 'Divisi / Bagian', pengguna?.divisi?.nama ?? '-'),
                  _buildProfileRow(Icons.phone_android_outlined, 'Nomor Telepon', pengguna?.telepon ?? '-'),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // App Settings
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('PENGATURAN APLIKASI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8)),
                  const SizedBox(height: 10),

                  // Dark Mode Switch
                  Material(
                    type: MaterialType.transparency,
                    child: SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      secondary: Icon(isDark ? Icons.dark_mode_outlined : Icons.light_mode_outlined, color: AppColors.orangePrimary),
                      title: const Text('Tema Gelap (Dark Mode)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: Text(isDark ? 'Mode gelap aktif' : 'Mode terang aktif', style: const TextStyle(fontSize: 11)),
                      value: isDark,
                      onChanged: (val) => themeProvider.toggleTheme(val),
                    ),
                  ),

                  const Divider(),

                  // Server Configuration
                  Material(
                    type: MaterialType.transparency,
                    child: ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.dns_outlined, color: AppColors.navyPrimary),
                      title: const Text('Host Server API', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: Text(ApiEndpoints.baseUrl, style: const TextStyle(fontSize: 11), overflow: TextOverflow.ellipsis),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => _showEditServerDialog(context),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // System info
            Center(
              child: Column(
                children: [
                  Text('SI-ASET Mobile v1.0.0', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade600)),
                  Text('Laravel 12.70 Backend • MariaDB 11.8', style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Logout Button
            ElevatedButton.icon(
              onPressed: () => _confirmLogout(context),
              icon: const Icon(Icons.logout_rounded, color: Colors.white),
              label: const Text('Keluar dari Akun', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.statusDanger,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey),
          const SizedBox(width: 10),
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
