// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/core/config/app_config.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

/// Configuración de entorno: base URL del API Laravel.
///
/// Ajusta [apiBaseUrl] según dónde corre el backend (emulador Android usa
/// `http://10.0.2.2:8000` para apuntar al host; iOS simulador suele usar `localhost`).
class AppConfig {
  AppConfig._();

  /// Sin barra final. El prefijo `/api` se añade en [ApiConstants].
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000',
  );
}
