// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/features/usuarios/data/datasources/usuario_remote_datasource.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import '../../../../core/connection/api_client.dart';
import '../../../../core/constants/api_constants.dart';
import '../../../../core/errors/failures.dart';
import '../models/usuario_model.dart';

/// Fuente remota: llama al API Laravel (`GET /api/usuarios`).
class UsuarioRemoteDatasource {
  UsuarioRemoteDatasource({required ApiClient client}) : _client = client;

  final ApiClient _client;

  Future<List<UsuarioModel>> fetchUsuarios() async {
    try {
      final response = await _client.get(ApiConstants.usuarios);
      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw ServerFailure('Error HTTP ${response.statusCode}');
      }
      final map = decodeJsonMap(response);
      final raw = map['data'];
      if (raw is! List<dynamic>) {
        throw const ServerFailure('Formato de respuesta inválido');
      }
      return raw
          .map((e) => UsuarioModel.fromJson(e as Map<String, dynamic>))
          .toList();
    } on Failure {
      rethrow;
    } catch (e) {
      throw NetworkFailure('No se pudo conectar al servidor: $e');
    }
  }
}
