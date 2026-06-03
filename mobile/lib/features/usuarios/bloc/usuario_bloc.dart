// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/features/usuarios/bloc/usuario_bloc.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/errors/failures.dart';
import '../business/usuario_repository.dart';
import 'usuario_event.dart';
import 'usuario_state.dart';

class UsuarioBloc extends Bloc<UsuarioEvent, UsuarioState> {
  UsuarioBloc({required UsuarioRepository repository})
      : _repository = repository,
        super(const UsuarioInitial()) {
    on<UsuarioLoadRequested>(_onLoadRequested);
  }

  final UsuarioRepository _repository;

  Future<void> _onLoadRequested(
    UsuarioLoadRequested event,
    Emitter<UsuarioState> emit,
  ) async {
    emit(const UsuarioLoading());
    try {
      final list = await _repository.obtenerUsuarios();
      emit(UsuarioLoadSuccess(list));
    } on Failure catch (e) {
      emit(UsuarioLoadFailure(e.message));
    } catch (e) {
      emit(UsuarioLoadFailure(e.toString()));
    }
  }
}
