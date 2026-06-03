// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/features/usuarios/presentation/screens/usuario_list_screen.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/widgets/loading_indicator.dart';
import '../../bloc/usuario_bloc.dart';
import '../../bloc/usuario_event.dart';
import '../../bloc/usuario_state.dart';
import '../widgets/usuario_list_tile.dart';

/// Pantalla de lista de usuarios (UI): reacciona a estados del [UsuarioBloc].
class UsuarioListScreen extends StatefulWidget {
  const UsuarioListScreen({super.key});

  @override
  State<UsuarioListScreen> createState() => _UsuarioListScreenState();
}

class _UsuarioListScreenState extends State<UsuarioListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<UsuarioBloc>().add(const UsuarioLoadRequested());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Usuarios'),
      ),
      body: BlocBuilder<UsuarioBloc, UsuarioState>(
        builder: (context, state) {
          if (state is UsuarioInitial || state is UsuarioLoading) {
            return const LoadingIndicator();
          }
          if (state is UsuarioLoadFailure) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Padding(
                    padding: const EdgeInsets.all(24),
                    child: Text(
                      state.message,
                      textAlign: TextAlign.center,
                    ),
                  ),
                  TextButton(
                    onPressed: () => context.read<UsuarioBloc>().add(
                          const UsuarioLoadRequested(),
                        ),
                    child: const Text('Reintentar'),
                  ),
                ],
              ),
            );
          }
          if (state is UsuarioLoadSuccess) {
            final usuarios = state.usuarios;
            if (usuarios.isEmpty) {
              return const Center(child: Text('No hay usuarios'));
            }
            return RefreshIndicator(
              onRefresh: () async {
                context.read<UsuarioBloc>().add(const UsuarioLoadRequested());
              },
              child: ListView.builder(
                itemCount: usuarios.length,
                itemBuilder: (context, index) {
                  return UsuarioListTile(usuario: usuarios[index]);
                },
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
