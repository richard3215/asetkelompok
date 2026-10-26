import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/widgets/glass_card.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _identitasController = TextEditingController(text: 'admin@siaset.com');
  final TextEditingController _kataSandiController = TextEditingController(text: 'password123');
  bool _obscurePassword = true;

  void _pilihRoleDemo(String identitas, String peran) {
    setState(() {
      _identitasController.text = identitas;
      _kataSandiController.text = 'password123';
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Akun demo role $peran dipilih.'),
        duration: const Duration(seconds: 1),
      ),
    );
  }

  void _ubahServerUrl() {
    final controller = TextEditingController(text: ApiEndpoints.baseUrl);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Pengaturan Host Server API'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Untuk Android Emulator gunakan http://10.0.2.2:8000/api/v1\nUntuk Desktop/Web gunakan http://127.0.0.1:8000/api/v1',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: controller,
              decoration: const InputDecoration(labelText: 'URL Base API'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              setState(() {
                ApiEndpoints.baseUrl = controller.text.trim();
              });
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('URL API diatur ke: ${ApiEndpoints.baseUrl}')),
              );
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      body: Stack(
        children: [
          // Background Gradient & Abstract Orbs
          Container(
            decoration: BoxDecoration(
              gradient: isDark
                  ? const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [AppColors.navyDarkest, Color(0xFF0C2444)],
                    )
                  : const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFFE8EEF5), Color(0xFFF7F9FC)],
                    ),
            ),
          ),
          Positioned(
            top: -60,
            right: -60,
            child: Container(
              width: 240,
              height: 240,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.orangePrimary.withOpacity(isDark ? 0.2 : 0.15),
              ),
            ),
          ),
          Positioned(
            bottom: -80,
            left: -80,
            child: Container(
              width: 260,
              height: 260,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.navyPrimary.withOpacity(isDark ? 0.35 : 0.1),
              ),
            ),
          ),

          // Main Login Content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 20.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Header Logo & Branding
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: AppColors.navyPrimary.withOpacity(0.12),
                        border: Border.all(color: AppColors.orangePrimary.withOpacity(0.4), width: 2),
                      ),
                      child: const Icon(
                        Icons.account_balance_wallet_rounded,
                        size: 48,
                        color: AppColors.orangePrimary,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'SI-ASET',
                      style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                            letterSpacing: 1.5,
                            fontWeight: FontWeight.w900,
                            color: isDark ? Colors.white : AppColors.navyDark,
                          ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Sistem Informasi Manajemen Aset Perusahaan',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        color: isDark ? Colors.white70 : Colors.blueGrey.shade700,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Glassmorphic Login Card
                    GlassCard(
                      borderRadius: 24,
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Text(
                            'Masuk ke Akun',
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Gunakan Email atau NIP dan kata sandi Anda.',
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                          const SizedBox(height: 24),

                          // Identitas Input
                          TextField(
                            controller: _identitasController,
                            decoration: const InputDecoration(
                              labelText: 'Email atau NIP',
                              hintText: 'admin@siaset.com / 19850101001',
                              prefixIcon: Icon(Icons.person_outline),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Password Input
                          TextField(
                            controller: _kataSandiController,
                            obscureText: _obscurePassword,
                            decoration: InputDecoration(
                              labelText: 'Kata Sandi',
                              prefixIcon: const Icon(Icons.lock_outline),
                              suffixIcon: IconButton(
                                icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
                                onPressed: () {
                                  setState(() {
                                    _obscurePassword = !_obscurePassword;
                                  });
                                },
                              ),
                            ),
                          ),
                          const SizedBox(height: 24),

                          // Error Message if any
                          if (auth.errorMessage != null)
                            Container(
                              padding: const EdgeInsets.all(12),
                              margin: const EdgeInsets.only(bottom: 16),
                              decoration: BoxDecoration(
                                color: AppColors.statusDanger.withOpacity(0.12),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: AppColors.statusDanger.withOpacity(0.4)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.error_outline, color: AppColors.statusDanger, size: 20),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      auth.errorMessage!,
                                      style: const TextStyle(color: AppColors.statusDanger, fontSize: 12.5),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Submit Button
                          SizedBox(
                            height: 50,
                            child: ElevatedButton(
                              onPressed: auth.isLoading
                                  ? null
                                  : () async {
                                      final success = await auth.login(
                                        _identitasController.text,
                                        _kataSandiController.text,
                                      );
                                      if (!success && mounted && auth.errorMessage != null) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text(auth.errorMessage!),
                                            backgroundColor: AppColors.statusDanger,
                                          ),
                                        );
                                      }
                                    },
                              child: auth.isLoading
                                  ? const SizedBox(
                                      height: 22,
                                      width: 22,
                                      child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                                    )
                                  : const Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Flexible(
                                          child: Text(
                                            'Masuk Sekarang',
                                            style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        SizedBox(width: 8),
                                        Icon(Icons.arrow_forward_rounded, size: 18),
                                      ],
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Quick Role Switcher for Testing Demo
                    Text(
                      'PILIH ROLE DEMO CEPAT (1-KLIK):',
                      style: TextStyle(
                        fontSize: 11,
                        letterSpacing: 1,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white60 : Colors.blueGrey.shade600,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      alignment: WrapAlignment.center,
                      children: [
                        _buildRoleChip('Admin', 'admin@siaset.com', Icons.shield_outlined),
                        _buildRoleChip('Kepala Kantor', 'kepala.bekasi@siaset.com', Icons.account_balance_outlined),
                        _buildRoleChip('Manajer', 'manajer.aset@siaset.com', Icons.assignment_ind_outlined),
                        _buildRoleChip('Staf Teknisi', 'staf.budi@siaset.com', Icons.handyman_outlined),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Server Configuration Link
                    TextButton.icon(
                      onPressed: _ubahServerUrl,
                      icon: const Icon(Icons.settings_ethernet, size: 16),
                      label: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 220),
                        child: Text(
                          'Server: ${ApiEndpoints.baseUrl}',
                          style: const TextStyle(fontSize: 11.5),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRoleChip(String label, String email, IconData icon) {
    return ActionChip(
      avatar: Icon(icon, size: 16, color: AppColors.orangePrimary),
      label: Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
      onPressed: () => _pilihRoleDemo(email, label),
      backgroundColor: Theme.of(context).brightness == Brightness.dark
          ? AppColors.cardDark
          : Colors.white.withOpacity(0.9),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
    );
  }
}
