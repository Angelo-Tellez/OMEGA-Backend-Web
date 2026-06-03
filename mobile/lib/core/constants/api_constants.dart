// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/core/constants/api_constants.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

/// Rutas y cabeceras del API REST (Laravel `routes/api.php`).
class ApiConstants {
  ApiConstants._();

  static const String apiPrefix = '/api';
  static const String usuarios = '$apiPrefix/usuarios';

  static Map<String, String> jsonHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
}
