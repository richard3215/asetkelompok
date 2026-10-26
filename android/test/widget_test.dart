import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:android/core/providers/auth_provider.dart';
import 'package:android/core/providers/theme_provider.dart';
import 'package:android/models/pengguna_model.dart';
import 'package:android/screens/main_navigation_screen.dart';
import 'package:android/screens/penugasan/penugasan_form_screen.dart';
import 'package:android/screens/penugasan/penugasan_list_screen.dart';
import 'package:android/screens/pengadaan/pengadaan_list_screen.dart';
import 'package:android/screens/bast/bast_list_screen.dart';
import 'package:android/screens/aset/aset_list_screen.dart';
import 'package:android/screens/laporan/laporan_screen.dart';
import 'package:android/screens/profil/profil_screen.dart';
import 'package:android/screens/auth/login_screen.dart';

void main() {
  final dummyUser = PenggunaModel.fromJson({
    'id': 3,
    'nip': '19850303003',
    'nama': 'Rina Wijaya, S.E. (Manajer Logistik & Aset Bekasi)',
    'email': 'manajer.aset@siaset.com',
    'peran': {'id': 3, 'kode_peran': 'manajer', 'nama_peran': 'Manajer Aset'},
    'kantor': {'id': 2, 'kode_kantor': '17113', 'nama_kantor': 'Kantor Cabang Bekasi Pasar Lama (Bagian Pelayanan Operasional Bekasi)'},
  });

  Widget buildTestableWidget(Widget child) {
    final authProvider = AuthProvider();
    authProvider.setPenggunaForTesting(dummyUser);

    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider.value(value: authProvider),
      ],
      child: MaterialApp(
        home: child,
      ),
    );
  }

  testWidgets('Test all main screens for RenderFlex overflow at 360x640', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    final screens = <String, Widget>{
      'MainNavigationScreen': const MainNavigationScreen(),
      'PenugasanListScreen': const PenugasanListScreen(),
      'PenugasanFormScreen': const PenugasanFormScreen(),
      'PengadaanListScreen': const PengadaanListScreen(),
      'BastListScreen': const BastListScreen(),
      'AsetListScreen': const AsetListScreen(),
      'LaporanScreen': const LaporanScreen(),
      'ProfilScreen': const ProfilScreen(),
      'LoginScreen': const LoginScreen(),
    };

    for (final entry in screens.entries) {
      await tester.pumpWidget(buildTestableWidget(entry.value));
      await tester.pump();
      final exception = tester.takeException();
      if (exception != null) {
        debugPrint('FAILED SCREEN: ${entry.key}');
        if (exception is FlutterError) {
          debugPrint(exception.toStringDeep());
        } else {
          debugPrint('$exception');
        }
      }
      expect(exception, isNull, reason: 'Screen ${entry.key} overflowed!');
    }
  });
}
