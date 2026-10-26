import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeProvider extends ChangeNotifier {
  static const String _keyTheme = 'is_dark_mode';
  bool _isDarkMode = false;

  bool get isDarkMode => _isDarkMode;
  ThemeMode get themeMode => _isDarkMode ? ThemeMode.dark : ThemeMode.light;

  ThemeProvider() {
    _loadTheme();
  }

  Future<void> _loadTheme() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _isDarkMode = prefs.getBool(_keyTheme) ?? false;
      notifyListeners();
    } catch (_) {
      // Default to light mode if shared_preferences native channel is not yet registered
    }
  }

  Future<void> toggleTheme([bool? value]) async {
    _isDarkMode = value ?? !_isDarkMode;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(_keyTheme, _isDarkMode);
    } catch (_) {}
    notifyListeners();
  }
}
