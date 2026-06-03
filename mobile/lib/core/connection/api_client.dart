// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/core/connection/api_client.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/app_config.dart';
import '../constants/api_constants.dart';

/// Cliente HTTP mínimo para el API Laravel (REST + JSON).
///
/// Centraliza URL base y cabeceras para que los datasources solo definan rutas.
class ApiClient {
  ApiClient({http.Client? httpClient}) : _client = httpClient ?? http.Client();

  final http.Client _client;

  Uri _uri(String path) {
    final base = AppConfig.apiBaseUrl.replaceAll(RegExp(r'/+$'), '');
    final p = path.startsWith('/') ? path : '/$path';
    return Uri.parse('$base$p');
  }

  Future<http.Response> get(String path) {
    return _client.get(
      _uri(path),
      headers: ApiConstants.jsonHeaders,
    );
  }
}

/// Decodifica cuerpo JSON con mensaje útil si falla.
Map<String, dynamic> decodeJsonMap(http.Response response) {
  final raw = response.body;
  if (raw.isEmpty) {
    return {};
  }
  final decoded = jsonDecode(raw);
  if (decoded is Map<String, dynamic>) {
    return decoded;
  }
  throw FormatException('Respuesta JSON inesperada', raw);
}
