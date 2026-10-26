import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String? _token;

  String? get token => _token;

  Future<void> setToken(String? token) async {
    _token = token;
    try {
      final prefs = await SharedPreferences.getInstance();
      if (token != null) {
        await prefs.setString('auth_token', token);
      } else {
        await prefs.remove('auth_token');
      }
    } catch (_) {
      // Fallback in-memory jika native plugin belum terkompilasi penuh
    }
  }

  Future<String?> loadToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _token = prefs.getString('auth_token');
    } catch (_) {
      // In-memory fallback
    }
    return _token;
  }

  Future<String?> getToken() async {
    if (_token != null && _token!.isNotEmpty) return _token;
    return await loadToken();
  }

  Map<String, String> _headers({bool isJson = true}) {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (isJson) {
      headers['Content-Type'] = 'application/json';
    }
    if (_token != null && _token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  Future<Map<String, dynamic>> get(String url, {Map<String, dynamic>? queryParams}) async {
    try {
      Uri uri = Uri.parse(url);
      if (queryParams != null && queryParams.isNotEmpty) {
        uri = uri.replace(queryParameters: queryParams.map((k, v) => MapEntry(k, v.toString())));
      }

      final response = await http.get(uri, headers: _headers()).timeout(const Duration(seconds: 15));
      return _processResponse(response);
    } on SocketException {
      throw 'Tidak dapat terhubung ke server backend. Pastikan server Laravel aktif.';
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> post(String url, [Map<String, dynamic>? body]) async {
    try {
      final uri = Uri.parse(url);
      final response = await http.post(
        uri,
        headers: _headers(),
        body: jsonEncode(body ?? {}),
      ).timeout(const Duration(seconds: 15));

      return _processResponse(response);
    } on SocketException {
      throw 'Tidak dapat terhubung ke server backend. Periksa koneksi jaringan Anda.';
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> put(String url, [Map<String, dynamic>? body]) async {
    try {
      final uri = Uri.parse(url);
      final response = await http.put(
        uri,
        headers: _headers(),
        body: jsonEncode(body ?? {}),
      ).timeout(const Duration(seconds: 15));

      return _processResponse(response);
    } on SocketException {
      throw 'Tidak dapat terhubung ke server backend.';
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> multipartPost(
    String url, {
    required Map<String, String> fields,
    File? file,
    String fileField = 'foto',
  }) async {
    try {
      final uri = Uri.parse(url);
      final request = http.MultipartRequest('POST', uri);

      // Add headers
      request.headers.addAll(_headers(isJson: false));

      // Add text fields
      request.fields.addAll(fields);

      // Add file
      if (file != null && await file.exists()) {
        final ext = file.path.split('.').last.toLowerCase();
        final mediaType = (ext == 'png') ? MediaType('image', 'png') : MediaType('image', 'jpeg');
        request.files.add(
          await http.MultipartFile.fromPath(
            fileField,
            file.path,
            contentType: mediaType,
          ),
        );
      }

      final streamedResponse = await request.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamedResponse);
      return _processResponse(response);
    } catch (e) {
      rethrow;
    }
  }

  Map<String, dynamic> _processResponse(http.Response response) {
    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      throw 'Format respons server tidak valid (${response.statusCode}).';
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (body is Map<String, dynamic>) {
        return body;
      }
      return {'status': 'success', 'data': body};
    } else {
      String pesan = 'Terjadi kesalahan pada server.';
      if (body is Map && body.containsKey('pesan')) {
        pesan = body['pesan'].toString();
      } else if (body is Map && body.containsKey('message')) {
        pesan = body['message'].toString();
      }
      throw pesan;
    }
  }
}
