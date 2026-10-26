import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/colors.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/services/api_service.dart';
import '../../core/widgets/custom_badge.dart';
import '../../core/widgets/glass_card.dart';
import '../../models/penugasan_model.dart';

class PenugasanDetailScreen extends StatefulWidget {
  final PenugasanModel task;

  const PenugasanDetailScreen({super.key, required this.task});

  @override
  State<PenugasanDetailScreen> createState() => _PenugasanDetailScreenState();
}

class _PenugasanDetailScreenState extends State<PenugasanDetailScreen> {
  final ApiService _apiService = ApiService();
  late PenugasanModel _task;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _task = widget.task;
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiService.get('${ApiEndpoints.penugasan}/${_task.id}');
      if (res['data'] != null) {
        setState(() {
          _task = PenugasanModel.fromJson(res['data']);
        });
      }
    } catch (_) {
      // Keep existing data
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _submitKerjakan() async {
    setState(() => _isLoading = true);
    try {
      await _apiService.post(ApiEndpoints.penugasanKerjakan(_task.id), {
        'status_penugasan': 'dikerjakan',
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Status tugas diubah menjadi Sedang Dikerjakan')),
        );
      }
      _loadDetail();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showSubmitCompletionDialog() {
    final TextEditingController catatanController = TextEditingController();
    String selectedKondisiSesudah = 'baik';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Kirim Hasil Pekerjaan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Catatan Pelaksanaan:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                const SizedBox(height: 6),
                TextField(
                  controller: catatanController,
                  maxLines: 3,
                  decoration: const InputDecoration(
                    hintText: 'Uraikan hasil pengerjaan fisik atau perbaikan yang dilakukan...',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                const Text('Kondisi Aset Pasca Pekerjaan:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                DropdownButton<String>(
                  value: selectedKondisiSesudah,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 'sangat_baik', child: Text('Sangat Baik')),
                    DropdownMenuItem(value: 'baik', child: Text('Baik')),
                    DropdownMenuItem(value: 'rusak_ringan', child: Text('Rusak Ringan')),
                    DropdownMenuItem(value: 'rusak_berat', child: Text('Rusak Berat')),
                  ],
                  onChanged: (val) {
                    if (val != null) setDialogState(() => selectedKondisiSesudah = val);
                  },
                ),
                const SizedBox(height: 10),
                const Row(
                  children: [
                    Icon(Icons.photo_camera_outlined, size: 18, color: AppColors.orangePrimary),
                    SizedBox(width: 6),
                    Text('Bukti Foto Kegiatan Terlampir', style: TextStyle(fontSize: 11, fontStyle: FontStyle.italic)),
                  ],
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.statusSuccess),
              onPressed: () async {
                Navigator.pop(ctx);
                setState(() => _isLoading = true);
                try {
                  await _apiService.post(
                    ApiEndpoints.penugasanKirimValidasi(_task.id),
                    {
                      'catatan_pelaksanaan': catatanController.text,
                      'kondisi_sesudah': selectedKondisiSesudah,
                    },
                  );
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Pekerjaan dikirim untuk validasi atasan')),
                    );
                  }
                  _loadDetail();
                } catch (e) {
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
                    );
                  }
                } finally {
                  if (mounted) setState(() => _isLoading = false);
                }
              },
              child: const Text('Kirim ke Manajer', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      ),
    );
  }

  void _showValidationDialog(bool disetujui) {
    final TextEditingController alasanController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(
          disetujui ? 'Validasi & Setujui Tugas' : 'Kembalikan untuk Perbaikan',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: disetujui ? AppColors.statusSuccess : AppColors.statusDanger,
          ),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              disetujui
                  ? 'Pastikan bukti foto dan hasil pekerjaan staf telah diperiksa secara seksama.'
                  : 'Berikan alasan rinci mengapa pekerjaan ini perlu direvisi oleh staf:',
              style: const TextStyle(fontSize: 13),
            ),
            if (!disetujui) ...[
              const SizedBox(height: 10),
              TextField(
                controller: alasanController,
                maxLines: 2,
                decoration: const InputDecoration(
                  hintText: 'Contoh: Foto bukti kabel grounding belum terlampir',
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: disetujui ? AppColors.statusSuccess : AppColors.statusDanger,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              try {
                await _apiService.post(
                  ApiEndpoints.penugasanValidasi(_task.id),
                  {
                    'disetujui': disetujui,
                    'alasan_revisi': alasanController.text,
                  },
                );
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(disetujui ? 'Penugasan telah disetujui dan diselesaikan' : 'Penugasan dikembalikan ke staf'),
                    ),
                  );
                }
                _loadDetail();
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Gagal: $e'), backgroundColor: AppColors.statusDanger),
                  );
                }
              } finally {
                if (mounted) setState(() => _isLoading = false);
              }
            },
            child: Text(disetujui ? 'Setujui' : 'Kembalikan', style: const TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final pengguna = auth.pengguna;
    final isStaf = pengguna?.isStaf ?? true;
    final isManajerOrAdmin = (pengguna?.isManajer ?? false) || (pengguna?.isAdmin ?? false);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Penugasan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDetail,
          ),
        ],
      ),
      body: _isLoading && _task.items.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Task Info Header Card
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                _task.nomorPenugasan,
                                style: const TextStyle(color: AppColors.orangePrimary, fontWeight: FontWeight.bold, fontSize: 13),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 8),
                            CustomBadge(text: _task.statusPenugasan, type: BadgeType.status),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _task.instruksi,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        const Divider(),
                        _buildRow('Jenis Tugas', _task.jenisPenugasan.replaceAll('_', ' ').toUpperCase()),
                        _buildRow('Prioritas', _task.prioritas.toUpperCase()),
                        _buildRow('Pemberi Tugas', _task.namaPemberi ?? '-'),
                        _buildRow('Pelaksana (Staf)', _task.namaStaf ?? '-'),
                        _buildRow('Tanggal Penugasan', _task.tanggalPenugasan),
                        _buildRow('Tenggat Waktu', _task.tenggatWaktu),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Execution Notes & Rejection info
                  if (_task.catatanPelaksanaan != null && _task.catatanPelaksanaan!.isNotEmpty)
                    GlassCard(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.notes_rounded, size: 18, color: AppColors.orangePrimary),
                              SizedBox(width: 6),
                              Text('Catatan Pelaksanaan Staf', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(_task.catatanPelaksanaan!, style: const TextStyle(fontSize: 13)),
                        ],
                      ),
                    ),

                  if (_task.alasanRevisi != null && _task.alasanRevisi!.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.statusDanger.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.statusDanger.withValues(alpha: 0.3)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.warning_amber_rounded, size: 18, color: AppColors.statusDanger),
                              SizedBox(width: 6),
                              Text('Catatan Revisi dari Atasan:', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.statusDanger)),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(_task.alasanRevisi!, style: const TextStyle(fontSize: 13)),
                        ],
                      ),
                    ),
                  ],

                  const SizedBox(height: 16),

                  // Target Asset List
                  const Text('DAFTAR ASET TERKAIT TUGAS', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 0.8)),
                  const SizedBox(height: 8),
                  if (_task.items.isEmpty)
                    const Text('Belum ada rincian aset.', style: TextStyle(fontStyle: FontStyle.italic, color: Colors.grey))
                  else
                    ..._task.items.map((item) => GlassCard(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(12),
                          child: Row(
                            children: [
                              const Icon(Icons.devices, color: AppColors.navyPrimary),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(item.namaAset ?? 'Aset #${item.asetId}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                    Text(item.nomorIndex ?? '-', style: const TextStyle(color: AppColors.orangePrimary, fontSize: 11)),
                                  ],
                                ),
                              ),
                              if (item.kondisiSebelum != null)
                                Text('Kondisi: ${item.kondisiSebelum}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            ],
                          ),
                        )),

                  const SizedBox(height: 24),

                  // ACTION BUTTONS FOR STAF
                  if (isStaf) ...[
                    if (_task.statusPenugasan == 'diberikan' || _task.statusPenugasan == 'perlu_revisi')
                      ElevatedButton.icon(
                        onPressed: _submitKerjakan,
                        icon: const Icon(Icons.play_arrow_rounded, color: Colors.white),
                        label: const Text('Mulai Kerjakan Tugas Ini', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.orangePrimary,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    if (_task.statusPenugasan == 'dikerjakan')
                      ElevatedButton.icon(
                        onPressed: _showSubmitCompletionDialog,
                        icon: const Icon(Icons.check_circle_outline, color: Colors.white),
                        label: const Text('Kirim Hasil & Bukti Pekerjaan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.statusSuccess,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                  ],

                  // ACTION BUTTONS FOR MANAJER (VALIDATION)
                  if (isManajerOrAdmin && _task.statusPenugasan == 'menunggu_validasi') ...[
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _showValidationDialog(false),
                            icon: const Icon(Icons.replay, color: AppColors.statusDanger),
                            label: const Text('Minta Revisi', style: TextStyle(color: AppColors.statusDanger)),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: AppColors.statusDanger),
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () => _showValidationDialog(true),
                            icon: const Icon(Icons.verified, color: Colors.white),
                            label: const Text('Validasi & Selesai', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.statusSuccess,
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _buildRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
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
