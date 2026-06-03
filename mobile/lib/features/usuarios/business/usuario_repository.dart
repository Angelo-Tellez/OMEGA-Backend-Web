// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/features/usuarios/business/usuario_repository.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import '../data/models/usuario_model.dart';

/// Contrato de acceso a datos del dominio **Usuario** (la UI depende de esto, no de HTTP).
abstract class UsuarioRepository {
  Future<List<UsuarioModel>> obtenerUsuarios();
}
