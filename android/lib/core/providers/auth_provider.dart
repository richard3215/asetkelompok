import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_endpoints.dart';
import '../services/api_service.dart';
import '../../models/pengguna_model.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  PenggunaModel? _pengguna;
  bool _isLoading = true;
  String? _errorMessage;

  PenggunaModel? get pengguna => _pengguna;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _pengguna != null;
  String? get errorMessage => _errorMessage;

  AuthProvider() {
    loadStoredSession();
  }

  Future<void> loadStoredSession() async {
    _isLoading = true;
    notifyListeners();

    try {
      final token = await _apiService.loadToken();
      if (token != null && token.isNotEmpty) {
        try {
          final prefs = await SharedPreferences.getInstance();
          final userDataString = prefs.getString('user_data');
          if (userDataString != null) {
            _pengguna = PenggunaModel.fromJson(jsonDecode(userDataString));
          }
        } catch (_) {}

        // Ambil profil terbaru dari API
        try {
          final res = await _apiService.get(ApiEndpoints.profil);
          if (res['data'] != null) {
            _pengguna = PenggunaModel.fromJson(res['data']);
            try {
              final prefs = await SharedPreferences.getInstance();
              await prefs.setString('user_data', jsonEncode(_pengguna!.toJson()));
            } catch (_) {}
          }
        } catch (_) {
          // Tetap gunakan sesi offline/in-memory jika ada
        }
      }
    } catch (_) {
      await _apiService.setToken(null);
      _pengguna = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> login(String identitas, String kataSandi) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _apiService.post(ApiEndpoints.login, {
        'identitas': identitas.trim(),
        'kata_sandi': kataSandi,
      });

      if (res['status'] == 'success' && res['data'] != null) {
        final token = res['data']['token'];
        final userData = res['data']['pengguna'];

        await _apiService.setToken(token);
        _pengguna = PenggunaModel.fromJson(userData);

        try {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('user_data', jsonEncode(_pengguna!.toJson()));
        } catch (_) {
          // Fallback in-memory jika native plugin belum terkompilasi
        }

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = res['message'] ?? 'Login gagal. Periksa data Anda.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _apiService.post(ApiEndpoints.logout, {});
    } catch (_) {
      // Abaikan jika network error saat logout
    } finally {
      await _apiService.setToken(null);
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('user_data');
      } catch (_) {}
      _pengguna = null;
      _isLoading = false;
      notifyListeners();
    }
  }

  void setPenggunaForTesting(PenggunaModel pengguna) {
    _pengguna = pengguna;
    _isLoading = false;
    notifyListeners();
  }
}

