// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/features/usuarios/presentation/widgets/usuario_list_tile.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import 'package:flutter/material.dart';

import '../../data/models/usuario_model.dart';

/// Fila de lista para un usuario (presentación pura).
class UsuarioListTile extends StatelessWidget {
  const UsuarioListTile({super.key, required this.usuario});

  final UsuarioModel usuario;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: CircleAvatar(
        child: Text(usuario.nombre.isNotEmpty ? usuario.nombre[0].toUpperCase() : '?'),
      ),
      title: Text(usuario.nombre),
      subtitle: Text(usuario.email),
    );
  }
}
