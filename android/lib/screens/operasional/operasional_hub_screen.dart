import 'package:flutter/material.dart';
import '../../core/constants/colors.dart';
import '../bast/bast_list_screen.dart';
import '../pengadaan/pengadaan_list_screen.dart';
import '../penugasan/penugasan_list_screen.dart';

class OperasionalHubScreen extends StatefulWidget {
  final int initialSubTab;
  const OperasionalHubScreen({super.key, this.initialSubTab = 0});

  @override
  State<OperasionalHubScreen> createState() => _OperasionalHubScreenState();
}

class _OperasionalHubScreenState extends State<OperasionalHubScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this, initialIndex: widget.initialSubTab);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Operasional & Dokumen'),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.orangePrimary,
          labelColor: AppColors.orangePrimary,
          unselectedLabelColor: isDark ? Colors.white60 : Colors.black54,
          isScrollable: false,
          tabs: const [
            Tab(icon: Icon(Icons.task_alt_rounded, size: 20), text: 'Penugasan'),
            Tab(icon: Icon(Icons.shopping_bag_outlined, size: 20), text: 'Pengadaan'),
            Tab(icon: Icon(Icons.description_outlined, size: 20), text: 'BAST'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: const [
          PenugasanListScreen(),
          PengadaanListScreen(),
          BastListScreen(),
        ],
      ),
    );
  }
}
