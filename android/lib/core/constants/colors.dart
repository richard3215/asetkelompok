import 'package:flutter/material.dart';

class AppColors {
  // Navy Brand Palette
  static const Color navyDarkest = Color(0xFF05162A);
  static const Color navyDark = Color(0xFF0B2545);
  static const Color navyPrimary = Color(0xFF13315C);
  static const Color navyLight = Color(0xFF134074);
  static const Color navyLighter = Color(0xFF1D5A9C);
  static const Color navySurfaceLight = Color(0xFFF0F4F8);

  // Vibrant Orange Accent Palette
  static const Color orangePrimary = Color(0xFFFF7A00);
  static const Color orangeLight = Color(0xFFFF9429);
  static const Color orangeDark = Color(0xFFE05E00);
  static const Color orangeAccent = Color(0xFFFFB703);

  // Gradients
  static const LinearGradient navyGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFF0B2545), Color(0xFF134074)],
  );

  static const LinearGradient orangeGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFFFF7A00), Color(0xFFFF5400)],
  );

  static const LinearGradient glassGradientLight = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xCCFFFFFF),
      Color(0x99F0F4F8),
    ],
  );

  static const LinearGradient glassGradientDark = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xCC13315C),
      Color(0x990B2545),
    ],
  );

  // Semantic Status Colors
  static const Color statusSuccess = Color(0xFF00A86B);
  static const Color statusWarning = Color(0xFFFF9F1C);
  static const Color statusDanger = Color(0xFFE63946);
  static const Color statusInfo = Color(0xFF2A9D8F);

  // Backgrounds
  static const Color bgLight = Color(0xFFF8FAFC);
  static const Color bgDark = Color(0xFF07192F);

  // Card Backgrounds
  static const Color cardLight = Colors.white;
  static const Color cardDark = Color(0xFF0E2A4D);
}
