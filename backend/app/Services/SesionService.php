<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/SesionService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Grupo;
use App\Models\Sesion;
use App\Models\Usuario;
use App\Repositories\Contracts\AsistenciaRepositoryInterface;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\GrupoAlumnoRepositoryInterface;
use App\Repositories\Contracts\SesionRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SesionService
{
    public function __construct(
        private readonly SesionRepositoryInterface      $sesiones,
        private readonly GrupoRepositoryInterface       $grupos,
        private readonly AsistenciaRepositoryInterface  $asistencias,
        private readonly GrupoAlumnoRepositoryInterface $grupoAlumnos,
    ) {}

    /**
     * RF-62 — Lista sesiones de un grupo (solo el docente propietario).
     */
    public function listar(int $idGrupo, Usuario $docente): array
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        $this->verificarPropietarioGrupo($grupo, $docente);

        return $this->sesiones->todasPorGrupo($idGrupo)
            ->map(fn(Sesion $s) => $this->serializar($s))
            ->values()
            ->all();
    }

    /**
     * RF-63 — Consulta la sesión activa de un grupo.
     * Usada por Flutter para saber si ya hay una sesión abierta
     * y mostrar la clave y el temporizador (RF-47, RF-48).
     * Retorna null si no hay sesión activa.
     */
    public function sesionActivaDelGrupo(int $idGrupo, Usuario $docente): ?array
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        $this->verificarPropietarioGrupo($grupo, $docente);

        $sesion = $this->sesiones->buscarActivaPorGrupo($idGrupo);

        if (!$sesion) {
            return null;
        }

        return $this->serializarConEstadisticas($sesion);
    }

    /**
     * RF-62, RF-63 — Abre una nueva sesión y genera la clave única.
     */
    public function abrir(int $idGrupo, array $entrada, Usuario $docente): array
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        $this->verificarPropietarioGrupo($grupo, $docente);

        $sesionActiva = $this->sesiones->buscarActivaPorGrupo($idGrupo);
        if ($sesionActiva) {
            throw ValidationException::withMessages([
                'sesion' => ['Ya existe una sesión activa para este grupo.'],
            ]);
        }

        $validator = Validator::make($entrada, [
            'fec_sesion' => ['nullable', 'date'],
        ], [
            'fec_sesion.required' => 'La fecha de sesión es obligatoria.',
            'fec_sesion.date'     => 'La fecha de sesión no tiene un formato válido.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // RF-62, RNF-W-44 — Clave alfanumérica de 6 caracteres, única e irrepetible
        $clave = strtoupper(Str::random(6));

        // Si no viene fec_sesion, usar la fecha de hoy
        if (empty($entrada['fec_sesion'])) {
            $entrada['fec_sesion'] = now()->toDateString();
        }

        $sesion = $this->sesiones->crear([
            'id_grupo'      => $idGrupo,
            'clave'         => $clave,
            'est_sesion'    => 1,
            'fec_sesion'    => $entrada['fec_sesion'],
            'hora_apertura' => now(),
            'hora_cierre'   => null,
        ]);

        return $this->serializarConEstadisticas($sesion);
    }

    /**
     * RF-64 — Cierra manualmente la sesión activa.
     * La clave se invalida (null) al cerrar. est_sesion = 0.
     */
    public function cerrar(Sesion $sesion, Usuario $docente): array
    {
        $grupo = $this->grupos->buscarPorId($sesion->id_grupo);
        $this->verificarPropietarioGrupo($grupo, $docente);

        if ($sesion->est_sesion === 0) {
            throw ValidationException::withMessages([
                'sesion' => ['La sesión ya está cerrada.'],
            ]);
        }

        $this->sesiones->guardar($sesion, [
            'est_sesion'  => 0,
            'clave'       => null,
            'hora_cierre' => now(),
        ]);

        // RF-64, RF-22 — Registrar ausentes automáticamente al cerrar sesión
        // Todos los alumnos del grupo que NO registraron asistencia quedan como Ausente (2)
        $alumnosGrupo = $this->grupoAlumnos->alumnosPorGrupo($sesion->id_grupo);
        foreach ($alumnosGrupo as $vinculacion) {
            $asistenciaExistente = $this->asistencias->buscarPorSesionYAlumno(
                $sesion->id_sesion,
                $vinculacion->id_alumno
            );
            if (!$asistenciaExistente) {
                $this->asistencias->crear([
                    'id_sesion'      => $sesion->id_sesion,
                    'id_alumno'      => $vinculacion->id_alumno,
                    'est_asistencia' => 2, // Ausente
                    'hora_registro'  => null,
                ]);
            }
        }

        return $this->serializar($sesion->fresh());
    }

    /**
     * RF-63 — Obtiene datos de una sesión específica con estadísticas.
     */
    public function obtener(Sesion $sesion, Usuario $docente): array
    {
        $grupo = $this->grupos->buscarPorId($sesion->id_grupo);
        $this->verificarPropietarioGrupo($grupo, $docente);

        $base        = $this->serializarConEstadisticas($sesion);
        $asistencias = $this->asistencias->todasPorSesion($sesion->id_sesion);

        // Incluir lista completa de asistencias para el detalle en la app
        $base['asistencias'] = $asistencias->map(fn($a) => [
            'id_asistencia'  => $a->id_asistencia,
            'id_alumno'      => $a->id_alumno,
            'nombre_alumno'  => $a->alumno
                ? "{$a->alumno->ap_pat} {$a->alumno->ap_mat}, {$a->alumno->nombre}"
                : null,
            'est_asistencia' => $a->est_asistencia,
            'hora_registro'  => $a->hora_registro?->format('H:i'),
        ])->values()->all();

        return $base;
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers privados
    // ─────────────────────────────────────────────────────────────

    private function verificarPropietarioGrupo(?Grupo $grupo, Usuario $docente): void
    {
        if (!$grupo || $grupo->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException(
                'No tienes permiso para acceder a este grupo.'
            );
        }
    }

    /**
     * Serialización base — sin estadísticas (para listas).
     * La clave solo se expone cuando est_sesion = 1 (Activa).
     */
    private function serializar(Sesion $sesion): array
    {
        // Calcular estadísticas de asistencia para el historial
        $asistencias  = $sesion->asistencias ?? \App\Models\Asistencia::where('id_sesion', $sesion->id_sesion)->get();
        $presentes    = $asistencias->where('est_asistencia', 1)->count();
        $faltas       = $asistencias->where('est_asistencia', 2)->count();
        $justificadas = $asistencias->where('est_asistencia', 3)->count();
        $totalAlumnos = \App\Models\GrupoAlumno::where('id_grupo', $sesion->id_grupo)->count();

        return [
            'id_sesion'     => $sesion->id_sesion,
            'id_grupo'      => $sesion->id_grupo,
            'clave'         => $sesion->est_sesion === 1 ? $sesion->clave : null,
            'est_sesion'    => $sesion->est_sesion,
            'fec_sesion'    => $sesion->fec_sesion?->toDateString(),
            'hora_apertura' => $sesion->hora_apertura?->toIso8601String(),
            'hora_cierre'   => $sesion->hora_cierre?->toIso8601String(),
            'total_alumnos' => $totalAlumnos,
            'presentes'     => $presentes,
            'faltas'        => $faltas,
            'justificadas'  => $justificadas,
        ];
    }

    /**
     * RF-48, RF-49 — Serialización con estadísticas en tiempo real.
     * Incluye: total_alumnos, presentes, pendientes y segundos transcurridos
     * para el temporizador del docente en Flutter.
     */
    private function serializarConEstadisticas(Sesion $sesion): array
    {
        $base        = $this->serializar($sesion);
        $asistencias = $this->asistencias->todasPorSesion($sesion->id_sesion);
        $totalGrupo  = $this->grupoAlumnos->alumnosPorGrupo($sesion->id_grupo)->count();
        $presentes   = $asistencias->where('est_asistencia', 1)->count();

        // RF-48 — Segundos transcurridos desde apertura (para el temporizador MM:SS)
        $segundos = $sesion->hora_apertura
            ? (int) $sesion->hora_apertura->diffInSeconds(now())
            : 0;

        return array_merge($base, [
            // RF-49 — Estadísticas en tiempo real
            'total_alumnos'    => $totalGrupo,
            'presentes'        => $presentes,
            'pendientes'       => max(0, $totalGrupo - $presentes),
            'porcentaje_reg'   => $totalGrupo > 0
                ? round(($presentes / $totalGrupo) * 100, 1)
                : 0.0,
            // RF-48 — Para el temporizador de Flutter
            'segundos_abierta' => $segundos,
            // Últimos 5 registros (RF-50)
            'ultimos_registros' => $asistencias
                ->where('est_asistencia', 1)
                ->sortByDesc('hora_registro')
                ->take(5)
                ->map(fn($a) => [
                    'id_alumno'     => $a->id_alumno,
                    'nombre'        => $a->alumno
                        ? "{$a->alumno->ap_pat}, {$a->alumno->nombre}"
                        : null,
                    'hora_registro' => $a->hora_registro?->format('H:i:s'),
                ])->values()->all(),
        ]);
    }
}
