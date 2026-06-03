// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/features/usuarios/data/repositories/usuario_repository_impl.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import '../../business/usuario_repository.dart';
import '../datasources/usuario_remote_datasource.dart';
import '../models/usuario_model.dart';

/// Implementación del repositorio: delega en la fuente remota (inyectable / testeable).
class UsuarioRepositoryImpl implements UsuarioRepository {
  UsuarioRepositoryImpl({required UsuarioRemoteDatasource remote})
      : _remote = remote;

  final UsuarioRemoteDatasource _remote;

  @override
  Future<List<UsuarioModel>> obtenerUsuarios() => _remote.fetchUsuarios();
}
