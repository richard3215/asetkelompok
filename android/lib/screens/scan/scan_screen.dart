import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/services/api_service.dart';
import '../../models/aset_model.dart';
import '../aset/aset_detail_screen.dart';

class ScanScreen extends StatefulWidget {
  const ScanScreen({super.key});

  @override
  State<ScanScreen> createState() => _ScanScreenState();
}

class _ScanScreenState extends State<ScanScreen> {
  final ApiService _apiService = ApiService();
  late MobileScannerController _scannerController;
  bool _isProcessing = false;
  bool _isTorchOn = false;
  final TextEditingController _manualController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _scannerController = MobileScannerController(
      detectionSpeed: DetectionSpeed.normal,
      facing: CameraFacing.back,
      torchEnabled: false,
    );
  }

  @override
  void dispose() {
    _scannerController.dispose();
    _manualController.dispose();
    super.dispose();
  }

  Future<void> _toggleTorch() async {
    try {
      await _scannerController.toggleTorch();
      setState(() => _isTorchOn = !_isTorchOn);
    } catch (_) {}
  }

  Future<void> _handleBarcodeScan(String rawCode) async {
    if (_isProcessing) return;
    setState(() => _isProcessing = true);

    final cleanCode = rawCode.trim();
    if (cleanCode.isEmpty) {
      setState(() => _isProcessing = false);
      return;
    }

    try {
      final res = await _apiService.get('${ApiEndpoints.scanAset}/$cleanCode');
      if (!mounted) return;

      if (res['data'] != null) {
        final aset = AsetModel.fromJson(res['data']);
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => AsetDetailScreen(aset: aset)),
        ).then((_) {
          if (mounted) setState(() => _isProcessing = false);
        });
      } else {
        _showNotFoundDialog(cleanCode);
      }
    } catch (e) {
      if (mounted) {
        _showNotFoundDialog(cleanCode, errorMsg: e.toString());
      }
    }
  }

  void _showNotFoundDialog(String code, {String? errorMsg}) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: AppColors.statusDanger),
            SizedBox(width: 8),
            Text('Aset Tidak Ditemukan', style: TextStyle(fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Kode yang dipindai:\n"$code"', style: const TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 10),
            Text(
              errorMsg ?? 'Nomor index atau barcode ini tidak terdaftar dalam basis data SI-ASET perusahaan.',
              style: const TextStyle(fontSize: 13),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() => _isProcessing = false);
            },
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  void _showManualInputDialog() {
    _manualController.clear();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Input Kode Manual', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Masukkan Nomor Index Aset atau Barcode Vendor:',
              style: TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _manualController,
              autofocus: true,
              decoration: const InputDecoration(
                hintText: 'Contoh: AST/17000/202610/00001',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.keyboard),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.orangePrimary),
            onPressed: () {
              final code = _manualController.text.trim();
              Navigator.pop(ctx);
              if (code.isNotEmpty) {
                _handleBarcodeScan(code);
              }
            },
            child: const Text('Cari Aset', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pindai QR / Barcode'),
        actions: [
          IconButton(
            icon: Icon(
              _isTorchOn ? Icons.flash_on : Icons.flash_off,
              color: _isTorchOn ? Colors.amber : null,
            ),
            tooltip: 'Lampu Kilat',
            onPressed: _toggleTorch,
          ),
          IconButton(
            icon: const Icon(Icons.cameraswitch_rounded),
            tooltip: 'Ganti Kamera',
            onPressed: () => _scannerController.switchCamera(),
          ),
        ],
      ),
      body: Stack(
        children: [
          // Mobile Scanner Camera View
          MobileScanner(
            controller: _scannerController,
            onDetect: (capture) {
              final barcodes = capture.barcodes;
              for (final barcode in barcodes) {
                final rawValue = barcode.rawValue;
                if (rawValue != null && rawValue.isNotEmpty) {
                  _handleBarcodeScan(rawValue);
                  break;
                }
              }
            },
            errorBuilder: (context, error) {
              return Center(
                child: Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.videocam_off_outlined, size: 56, color: Colors.grey),
                      const SizedBox(height: 14),
                      const Text(
                        'Kamera tidak aktif atau izin belum diberikan.',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Gunakan tombol input manual di bawah untuk memeriksa aset berdasarkan nomor index.',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                      const SizedBox(height: 18),
                      ElevatedButton.icon(
                        onPressed: _showManualInputDialog,
                        icon: const Icon(Icons.edit, color: Colors.white),
                        label: const Text('Input Manual Kode Aset', style: TextStyle(color: Colors.white)),
                        style: ElevatedButton.styleFrom(backgroundColor: AppColors.orangePrimary),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),

          // Scanning Overlay Box with Viewfinder Frame
          Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.orangePrimary, width: 2.5),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.orangePrimary.withValues(alpha: 0.15),
                    blurRadius: 15,
                    spreadRadius: 2,
                  ),
                ],
              ),
              child: Stack(
                children: [
                  Positioned(
                    top: 10,
                    left: 10,
                    child: Container(width: 20, height: 20, decoration: const BoxDecoration(border: Border(top: BorderSide(color: Colors.white, width: 3), left: BorderSide(color: Colors.white, width: 3)))),
                  ),
                  Positioned(
                    top: 10,
                    right: 10,
                    child: Container(width: 20, height: 20, decoration: const BoxDecoration(border: Border(top: BorderSide(color: Colors.white, width: 3), right: BorderSide(color: Colors.white, width: 3)))),
                  ),
                  Positioned(
                    bottom: 10,
                    left: 10,
                    child: Container(width: 20, height: 20, decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white, width: 3), left: BorderSide(color: Colors.white, width: 3)))),
                  ),
                  Positioned(
                    bottom: 10,
                    right: 10,
                    child: Container(width: 20, height: 20, decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white, width: 3), right: BorderSide(color: Colors.white, width: 3)))),
                  ),
                ],
              ),
            ),
          ),

          // Processing indicator
          if (_isProcessing)
            Container(
              color: Colors.black54,
              child: const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(color: AppColors.orangePrimary),
                    SizedBox(height: 12),
                    Text('Mencari data aset...', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ),

          // Bottom Bar for Manual Input
          Positioned(
            bottom: 24,
            left: 20,
            right: 20,
            child: ElevatedButton.icon(
              onPressed: _showManualInputDialog,
              icon: const Icon(Icons.edit_note_rounded, color: Colors.white),
              label: const Text('Input Nomor Index / Barcode Manual', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.navyPrimary,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
