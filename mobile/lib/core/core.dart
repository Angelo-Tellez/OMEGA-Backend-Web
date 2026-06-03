// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : mobile/lib/core/core.dart
// Created on : 27/04/2026
// Created by : Jorge Alejandro Martinez Toris
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Jorge Alejandro Martinez Toris - Creacion del archivo
// ============================================================

/*
  Núcleo compartido de la app (transversal a todos los features).

  Carpetas:
  - config: URLs base, entorno, flags.
  - connection: cliente HTTP y manejo de peticiones.
  - constants: claves y valores fijos reutilizables.
  - errors: fallos y mensajes de error homogéneos.
  - theme: tema Material y estilos globales.
  - usecases: contratos base para casos de uso (opcional / escalable).
  - widgets: componentes UI reutilizables sin lógica de negocio.
*/

export 'config/app_config.dart';
export 'connection/api_client.dart';
export 'constants/api_constants.dart';
export 'errors/failures.dart';
export 'theme/app_theme.dart';
export 'usecases/usecase.dart';
export 'widgets/loading_indicator.dart';
