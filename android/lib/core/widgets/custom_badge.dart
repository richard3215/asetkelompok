import 'package:flutter/material.dart';
import '../constants/colors.dart';

enum BadgeType { status, kondisi, kepemilikan }

class CustomBadge extends StatelessWidget {
  final String text;
  final Color? backgroundColor;
  final Color? textColor;
  final BadgeType? type;
  final IconData? icon;

  const CustomBadge({
    super.key,
    required this.text,
    this.backgroundColor,
    this.textColor,
    this.type,
    this.icon,
  });

  factory CustomBadge.kondisi(String kondisi) {
    final colors = _getKondisiColors(kondisi);
    return CustomBadge(
      text: kondisi.replaceAll('_', ' ').toUpperCase(),
      backgroundColor: colors.bg,
      textColor: colors.fg,
    );
  }

  factory CustomBadge.kepemilikan(String jenis) {
    if (jenis == 'milik_sendiri') {
      return const CustomBadge(
        text: 'PERUSAHAAN',
        backgroundColor: Color(0xFFEAE6FF),
        textColor: Color(0xFF403294),
      );
    } else {
      return const CustomBadge(
        text: 'SEWA VENDOR',
        backgroundColor: Color(0xFFFFECE5),
        textColor: AppColors.orangeDark,
      );
    }
  }

  factory CustomBadge.status(String status) {
    final colors = _getStatusColors(status);
    return CustomBadge(
      text: status.replaceAll('_', ' ').toUpperCase(),
      backgroundColor: colors.bg,
      textColor: colors.fg,
    );
  }

  static ({Color bg, Color fg}) _getKondisiColors(String kondisi) {
    switch (kondisi.toLowerCase()) {
      case 'sangat_baik':
        return (bg: const Color(0xFFE3FCEF), fg: const Color(0xFF006644));
      case 'baik':
        return (bg: const Color(0xFFDEEBFF), fg: const Color(0xFF0747A6));
      case 'rusak_ringan':
        return (bg: const Color(0xFFFFF0B3), fg: const Color(0xFF172B4D));
      case 'rusak_berat':
        return (bg: const Color(0xFFFFEBE6), fg: const Color(0xFFBF2600));
      default:
        return (bg: Colors.grey.shade200, fg: Colors.grey.shade800);
    }
  }

  static ({Color bg, Color fg}) _getStatusColors(String status) {
    switch (status.toLowerCase()) {
      case 'aktif':
      case 'selesai':
      case 'disetujui':
      case 'terverifikasi':
      case 'final':
        return (bg: const Color(0xFFE3FCEF), fg: const Color(0xFF006644));
      case 'dalam_pemeliharaan':
      case 'sedang_dikerjakan':
      case 'dikerjakan':
      case 'menunggu_validasi':
      case 'menunggu_persetujuan':
      case 'diberikan':
        return (bg: const Color(0xFFFFF0B3), fg: const Color(0xFF8F4D00));
      case 'revisi':
      case 'perlu_revisi':
      case 'ditolak':
      case 'terlambat':
      case 'rusak':
      case 'hilang':
        return (bg: const Color(0xFFFFEBE6), fg: const Color(0xFFBF2600));
      default:
        return (bg: Colors.blueGrey.shade100, fg: Colors.blueGrey.shade900);
    }
  }

  @override
  Widget build(BuildContext context) {
    Color bg = backgroundColor ?? Colors.grey.shade200;
    Color fg = textColor ?? Colors.grey.shade800;

    if (type == BadgeType.kondisi) {
      final computed = _getKondisiColors(text);
      bg = backgroundColor ?? computed.bg;
      fg = textColor ?? computed.fg;
    } else if (type == BadgeType.status) {
      final computed = _getStatusColors(text);
      bg = backgroundColor ?? computed.bg;
      fg = textColor ?? computed.fg;
    } else if (type == BadgeType.kepemilikan) {
      if (text == 'milik_sendiri') {
        bg = backgroundColor ?? const Color(0xFFEAE6FF);
        fg = textColor ?? const Color(0xFF403294);
      } else {
        bg = backgroundColor ?? const Color(0xFFFFECE5);
        fg = textColor ?? AppColors.orangeDark;
      }
    }

    final labelWidget = Text(
      text.replaceAll('_', ' ').toUpperCase(),
      style: TextStyle(
        color: fg,
        fontSize: 10.5,
        fontWeight: FontWeight.bold,
        letterSpacing: 0.3,
      ),
      maxLines: 1,
      overflow: TextOverflow.ellipsis,
    );

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
      ),
      child: icon != null
          ? Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(icon, size: 12, color: fg),
                const SizedBox(width: 4),
                Flexible(child: labelWidget),
              ],
            )
          : labelWidget,
    );
  }
}
