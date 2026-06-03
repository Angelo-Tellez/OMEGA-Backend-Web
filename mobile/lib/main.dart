// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/main.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import 'core/connection/api_client.dart';
import 'core/theme/app_theme.dart';
import 'features/usuarios/bloc/usuario_bloc.dart';
import 'features/usuarios/data/datasources/usuario_remote_datasource.dart';
import 'features/usuarios/data/repositories/usuario_repository_impl.dart';
import 'features/usuarios/presentation/screens/usuario_list_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  final apiClient = ApiClient();
  final repository = UsuarioRepositoryImpl(
    remote: UsuarioRemoteDatasource(client: apiClient),
  );

  runApp(
    BlocProvider(
      create: (_) => UsuarioBloc(repository: repository),
      child: const ProyectoBApp(),
    ),
  );
}

class ProyectoBApp extends StatelessWidget {
  const ProyectoBApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Proyecto B',
      theme: AppTheme.light(),
      home: const UsuarioListScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}
