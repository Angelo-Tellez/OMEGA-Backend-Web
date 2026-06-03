// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/core/usecases/usecase.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

/// Contrato genérico para casos de uso (Clean Architecture / escalable).
///
/// Cada feature puede definir `class MiCasoDeUso implements UseCase<Salida, Entrada>`.
abstract class UseCase<Type, Params> {
  Future<Type> call(Params params);
}

/// Parámetro vacío cuando el caso de uso no recibe argumentos.
class NoParams {
  const NoParams();
}
