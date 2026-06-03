// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/test/widget_test.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:proyecto_b/features/usuarios/bloc/usuario_bloc.dart';
import 'package:proyecto_b/features/usuarios/business/usuario_repository.dart';
import 'package:proyecto_b/features/usuarios/data/models/usuario_model.dart';
import 'package:proyecto_b/features/usuarios/presentation/screens/usuario_list_screen.dart';

void main() {
  testWidgets('Lista vacía cuando el repositorio no devuelve usuarios', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        home: BlocProvider(
          create: (_) => UsuarioBloc(repository: _FakeUsuarioRepository()),
          child: const UsuarioListScreen(),
        ),
      ),
    );

    await tester.pumpAndSettle();
    expect(find.text('No hay usuarios'), findsOneWidget);
  });
}

class _FakeUsuarioRepository implements UsuarioRepository {
  @override
  Future<List<UsuarioModel>> obtenerUsuarios() async => [];
}
