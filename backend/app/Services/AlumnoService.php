<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/AlumnoService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\GrupoAlumno;
use App\Models\Usuario;
use App\Repositories\Contracts\AsistenciaRepositoryInterface;
use App\Repositories\Contracts\GrupoAlumnoRepositoryInterface;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\RubroEvaluacionRepositoryInterface;
use App\Repositories\Contracts\SesionRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AlumnoService
{
    public function __construct(
        private readonly GrupoAlumnoRepositoryInterface    $grupoAlumnos,
        private readonly GrupoRepositoryInterface          $grupos,
        private readonly SesionRepositoryInterface         $sesiones,
        private readonly AsistenciaRepositoryInterface     $asistencias,
        private readonly RubroEvaluacionRepositoryInterface $rubros,
    ) {}

    // ─────────────────────────────────────────────────────────────
    //  RF-19 — Matriculación por código de invitación
    // ─────────────────────────────────────────────────────────────
    public function unirse(array $entrada, Usuario $alumno): array
    {
        // Solo alumnos pueden matricularse
        if (!$alumno->isAlumno()) {
            throw new AuthorizationException('Solo los alumnos pueden unirse a un grupo.');
        }

        $validator = Validator::make($entrada, [
            'codigo_inv' => ['required', 'string', 'max:20'],
        ], [
            'codigo_inv.required' => 'El código de invitación es obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Buscar grupo por código
        $grupo = $this->grupos->buscarPorCodigoInv(
            strtoupper(trim($entrada['codigo_inv']))
        );

        if (!$grupo) {
            throw ValidationException::withMessages([
                'codigo_inv' => ['El código de invitación no existe o no es válido.'],
            ]);
        }

        // Verificar que el alumno no esté ya matriculado
        $vinculacion = $this->grupoAlumnos->buscarVinculacion(
            $grupo->id_grupo,
            $alumno->id_usuario
        );

        if ($vinculacion) {
            throw ValidationException::withMessages([
                'codigo_inv' => ['Ya estás matriculado en este grupo.'],
            ]);
        }

        // Verificar capacidad máxima (RF-19)
        $totalActual = $this->grupoAlumnos->alumnosPorGrupo($grupo->id_grupo)->count();
        if ($totalActual >= $grupo->no_alumnos) {
            throw ValidationException::withMessages([
                'codigo_inv' => ['El grupo ha alcanzado su capacidad máxima.'],
            ]);
        }

        $grupoAlumno = $this->grupoAlumnos->crear([
            'id_grupo'        => $grupo->id_grupo,
            'id_alumno'       => $alumno->id_usuario,
            'fec_inscripcion' => now()->toDateString(),
        ]);

        return [
            'id_grupo_alumno' => $grupoAlumno->id_grupo_alumno,
            'id_grupo'        => $grupoAlumno->id_grupo,
            'materia'         => $grupo->materia,
            'nombre_grupo'    => $grupo->nombre,
            'periodo'         => $grupo->periodo,
            'fec_inscripcion' => $grupoAlumno->fec_inscripcion?->toDateString(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  RF-15, RF-32, RF-33, RF-41, RF-43, RF-44, RF-45
    //  Panel de progreso — lista de materias con métricas
    // ─────────────────────────────────────────────────────────────
    public function misGrupos(Usuario $alumno): array
    {
        if (!$alumno->isAlumno()) {
            throw new AuthorizationException('Solo los alumnos pueden consultar su panel de progreso.');
        }

        $vinculaciones = $this->grupoAlumnos->gruposPorAlumno($alumno->id_usuario);

        return $vinculaciones->map(function (GrupoAlumno $ga) use ($alumno) {
            $grupo = $this->grupos->buscarPorId($ga->id_grupo);

            if (!$grupo) {
                return null;
            }

            // Obtener todas las sesiones cerradas del grupo
            $sesiones = $this->sesiones->todasPorGrupo($grupo->id_grupo)
                ->where('est_sesion', 0); // 0 = Cerrada (Diccionario de Datos §4.6)

            $totalSesiones = $sesiones->count();

            // Contar asistencias del alumno en este grupo
            $presentes    = 0;
            $ausentes     = 0;
            $justificadas = 0;

            foreach ($sesiones as $sesion) {
                $asistencia = $this->asistencias->buscarPorSesionYAlumno(
                    $sesion->id_sesion,
                    $alumno->id_usuario
                );

                if ($asistencia) {
                    match ($asistencia->est_asistencia) {
                        1 => $presentes++,    // Presente
                        2 => $ausentes++,     // Ausente
                        3 => $justificadas++, // Justificada
                        default => null,
                    };
                }
            }

            // RF-32 — Porcentaje: (Presentes + Justificadas) / Total sesiones
            $totalCuenta    = $presentes + $justificadas;
            $porcentajeReal = $totalSesiones > 0
                ? round(($totalCuenta / $totalSesiones) * 100, 2)
                : 0.0;

            // RF-33, RF-43, RF-45 — Evaluación por rubros de la institución
            $rubros       = $this->rubros->todosPorInstitucion($grupo->id_institucion);
            $estadoRubros = $rubros->map(function ($rubro) use ($porcentajeReal, $totalSesiones, $presentes, $justificadas) {
                $cumple = $porcentajeReal >= (float) $rubro->porcentaje_minimo;

                // RF-44 — Calcular faltas permitidas restantes
                $asistenciasNecesarias = ceil(($rubro->porcentaje_minimo / 100) * $totalSesiones);
                $faltasPermitidas      = $totalSesiones - $asistenciasNecesarias;
                $faltasActuales        = $totalSesiones - ($presentes + $justificadas);
                $faltasRestantes       = max(0, $faltasPermitidas - $faltasActuales);

                return [
                    'id_rubro'           => $rubro->id_rubro,
                    'nombre'             => $rubro->nombre,
                    'porcentaje_minimo'  => (float) $rubro->porcentaje_minimo,
                    'cumple'             => $cumple,
                    'faltas_restantes'   => $faltasRestantes,
                    // RF-44 — Alerta: riesgo próximo si quedan ≤ 2 faltas
                    'alerta'             => !$cumple ? 'limite_excedido'
                        : ($faltasRestantes <= 2 ? 'riesgo_proximo' : null),
                ];
            })->values()->all();

            return [
                // Identificadores
                'id_grupo'        => $grupo->id_grupo,
                'id_grupo_alumno' => $ga->id_grupo_alumno,
                'fec_inscripcion' => $ga->fec_inscripcion?->toDateString(),
                // Datos del aula (RF-39)
                'materia'         => $grupo->materia,
                'nombre_grupo'    => $grupo->nombre,
                'periodo'         => $grupo->periodo,
                // Métricas RF-32, RF-41
                'total_sesiones'  => $totalSesiones,
                'presentes'       => $presentes,
                'ausentes'        => $ausentes,
                'justificadas'    => $justificadas,
                'porcentaje'      => $porcentajeReal,
                // RF-33, RF-43, RF-45 — Rubros
                'rubros'          => $estadoRubros,
                'sesion_activa'   => $this->_getSesionActiva($grupo->id_grupo),
            ];
        })->filter()->values()->all();
    }

    private function _getSesionActiva(int $idGrupo): ?array
    {
        $sesion = $this->sesiones->buscarActivaPorGrupo($idGrupo);
        if (!$sesion) return null;
        return ["id_sesion" => $sesion->id_sesion, "clave" => $sesion->clave];
    }

    // ─────────────────────────────────────────────────────────────
    //  RF-14, RF-21, RF-22, RF-38 — Registro de asistencia con clave
    // ─────────────────────────────────────────────────────────────
    public function registrarAsistencia(array $entrada, Usuario $alumno): array
    {
        if (!$alumno->isAlumno()) {
            throw new AuthorizationException('Solo los alumnos pueden registrar asistencia.');
        }

        $validator = Validator::make($entrada, [
            'id_grupo' => ['required', 'integer'],
            'clave'    => ['required', 'string', 'max:20'],
        ], [
            'id_grupo.required' => 'El grupo es obligatorio.',
            'clave.required'    => 'La clave de asistencia es obligatoria.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificar que el alumno esté matriculado en el grupo
        $vinculacion = $this->grupoAlumnos->buscarVinculacion(
            $entrada['id_grupo'],
            $alumno->id_usuario
        );

        if (!$vinculacion) {
            throw ValidationException::withMessages([
                'id_grupo' => ['No estás matriculado en este grupo.'],
            ]);
        }

        // RF-21 — Validar sesión activa
        $sesion = $this->sesiones->buscarActivaPorGrupo($entrada['id_grupo']);

        if (!$sesion) {
            throw ValidationException::withMessages([
                'sesion' => ['No hay una sesión activa para este grupo.'],
            ]);
        }

        // RF-22 — Validar clave (insensible a mayúsculas/minúsculas)
        if (strtoupper(trim($entrada['clave'])) !== strtoupper($sesion->clave)) {
            throw ValidationException::withMessages([
                'clave' => ['La clave de asistencia es incorrecta.'],
            ]);
        }

        // RF-14 — Rechazar registro duplicado (unicidad sesión+alumno)
        $asistenciaExistente = $this->asistencias->buscarPorSesionYAlumno(
            $sesion->id_sesion,
            $alumno->id_usuario
        );

        if ($asistenciaExistente) {
            // Si ya está marcado como Presente, rechazar
            if ($asistenciaExistente->est_asistencia === 1) {
                throw ValidationException::withMessages([
                    'asistencia' => ['Ya registraste tu asistencia en esta sesión.'],
                ]);
            }

            // Si existe registro de Ausente, actualizar a Presente
            $this->asistencias->guardar($asistenciaExistente, [
                'est_asistencia' => 1,
                'hora_registro'  => now(),
            ]);

            return $this->serializarAsistencia($asistenciaExistente->fresh());
        }

        // Crear registro nuevo
        $asistencia = $this->asistencias->crear([
            'id_sesion'      => $sesion->id_sesion,
            'id_alumno'      => $alumno->id_usuario,
            'est_asistencia' => 1, // Presente
            'hora_registro'  => now(),
        ]);

        return $this->serializarAsistencia($asistencia);
    }

    // ─────────────────────────────────────────────────────────────
    //  RF-31, RF-32, RF-33 — Historial por grupo con estados
    // ─────────────────────────────────────────────────────────────
    public function historialGrupo(int $idGrupo, Usuario $alumno): array
    {
        if (!$alumno->isAlumno()) {
            throw new AuthorizationException('Solo los alumnos pueden consultar su historial.');
        }

        // Verificar que el alumno esté matriculado
        $vinculacion = $this->grupoAlumnos->buscarVinculacion($idGrupo, $alumno->id_usuario);

        if (!$vinculacion) {
            throw ValidationException::withMessages([
                'id_grupo' => ['No estás matriculado en este grupo.'],
            ]);
        }

        $grupo    = $this->grupos->buscarPorId($idGrupo);
        $sesiones = $this->sesiones->todasPorGrupo($idGrupo)
            ->where('est_sesion', 0) // solo cerradas
            ->sortBy('fec_sesion');

        $historial = $sesiones->map(function ($sesion) use ($alumno) {
            $asistencia = $this->asistencias->buscarPorSesionYAlumno(
                $sesion->id_sesion,
                $alumno->id_usuario
            );

            return [
                'id_sesion'      => $sesion->id_sesion,
                'fec_sesion'     => $sesion->fec_sesion?->toDateString(),
                'hora_apertura'  => $sesion->hora_apertura?->toTimeString(),
                'hora_cierre'    => $sesion->hora_cierre?->toTimeString(),
                // RF-31 — Código de colores: 1=Verde(Presente), 2=Rojo(Ausente), 3=Amarillo(Justificada)
                'est_asistencia' => $asistencia?->est_asistencia ?? 2, // sin registro = Ausente
                'hora_registro'  => $asistencia?->hora_registro?->toTimeString(),
            ];
        })->values()->all();

        // RF-45 — Rubros de evaluación de la institución
        $rubros = $this->rubros->todosPorInstitucion($grupo->id_institucion)
            ->map(fn($r) => [
                'id_rubro'          => $r->id_rubro,
                'nombre'            => $r->nombre,
                'porcentaje_minimo' => (float) $r->porcentaje_minimo,
            ])->values()->all();

        return [
            'id_grupo'    => $grupo->id_grupo,
            'materia'     => $grupo->materia,
            'nombre'      => $grupo->nombre,
            'periodo'     => $grupo->periodo,
            'historial'   => $historial,
            'rubros'      => $rubros,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers privados
    // ─────────────────────────────────────────────────────────────
    private function serializarAsistencia($asistencia): array
    {
        return [
            'id_asistencia'  => $asistencia->id_asistencia,
            'id_sesion'      => $asistencia->id_sesion,
            'id_alumno'      => $asistencia->id_alumno,
            'est_asistencia' => $asistencia->est_asistencia,
            'hora_registro'  => $asistencia->hora_registro?->toIso8601String(),
        ];
    }
}
