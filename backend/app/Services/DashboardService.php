<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/DashboardService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Asistencia;
use App\Models\Sesion;
use App\Models\Usuario;
use App\Repositories\Contracts\AsistenciaRepositoryInterface;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\GrupoAlumnoRepositoryInterface;
use App\Repositories\Contracts\RubroEvaluacionRepositoryInterface;
use App\Repositories\Contracts\SesionRepositoryInterface;

class DashboardService
{
    public function __construct(
        private readonly GrupoRepositoryInterface           $grupos,
        private readonly SesionRepositoryInterface          $sesiones,
        private readonly AsistenciaRepositoryInterface      $asistencias,
        private readonly GrupoAlumnoRepositoryInterface     $grupoAlumnos,
        private readonly RubroEvaluacionRepositoryInterface $rubros,
    ) {}

    /**
     * RF-76 — Métricas consolidadas para el dashboard del Docente.
     * Incluye: tarjetas de resumen, sesiones recientes y alumnos en riesgo.
     */
    public function resumen(Usuario $docente): array
    {
        $grupos    = $this->grupos->todosPorDocente($docente->id_usuario);
        $grupoIds  = $grupos->pluck('id_grupo')->toArray();

        if (empty($grupoIds)) {
            return $this->resumenVacio();
        }

        // ── Tarjeta 1: Aulas activas ──────────────────────────────────────
        $aulasActivas = $grupos->count();

        // ── Tarjeta 2: Sesiones del día ───────────────────────────────────
        $sesionesHoy = Sesion::query()
            ->whereIn('id_grupo', $grupoIds)
            ->whereDate('fec_sesion', today())
            ->count();

        // ── Tarjeta 3: Justificantes pendientes (est_asistencia = 2 Ausente,
        //    visible para que el docente decida justificar) ─────────────────
        $justificantesPendientes = Asistencia::query()
            ->whereIn('id_sesion', function ($q) use ($grupoIds) {
                $q->select('id_sesion')
                  ->from('sesiones')
                  ->whereIn('id_grupo', $grupoIds);
            })
            ->where('est_asistencia', 2) // Ausente — pendiente de justificar
            ->count();

        // ── Tarjeta 4 + Sección "Alumnos en riesgo" ───────────────────────
        $alumnosEnRiesgo = $this->calcularAlumnosEnRiesgo($grupos, $grupoIds);

        // ── Sección "Sesiones recientes" (últimas 5 sesiones cerradas) ─────
        $sesionesRecientes = Sesion::query()
            ->whereIn('id_grupo', $grupoIds)
            ->where('est_sesion', 0) // Cerradas
            ->orderByDesc('hora_cierre')
            ->limit(5)
            ->get()
            ->map(function (Sesion $s) {
                $grupo      = $this->grupos->buscarPorId($s->id_grupo);
                $asistencias = $this->asistencias->todasPorSesion($s->id_sesion);
                $presentes   = $asistencias->where('est_asistencia', 1)->count();
                $total       = $asistencias->count();

                return [
                    'id_sesion'     => $s->id_sesion,
                    'id_grupo'      => $s->id_grupo,
                    'materia'       => $grupo?->materia,
                    'nombre_grupo'  => $grupo?->nombre,
                    'fec_sesion'    => $s->fec_sesion?->toDateString(),
                    'hora_apertura' => $s->hora_apertura?->format('H:i'),
                    'hora_cierre'   => $s->hora_cierre?->format('H:i'),
                    'presentes'     => $presentes,
                    'total_alumnos' => $total,
                    'porcentaje'    => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
                ];
            })->values()->all();

        return [
            // RF-76 — Tarjetas de resumen
            'aulas_activas'            => $aulasActivas,
            'sesiones_hoy'             => $sesionesHoy,
            'alumnos_en_riesgo'        => count($alumnosEnRiesgo),
            'justificantes_pendientes' => $justificantesPendientes,
            // Secciones del dashboard
            'sesiones_recientes'       => $sesionesRecientes,
            'alumnos_riesgo_detalle'   => $alumnosEnRiesgo,
        ];
    }

    /**
     * RF-77 — Estado de cada alumno respecto a los rubros de evaluación.
     * Para la tabla: Alumno | Asistencias | Faltas | Justificadas | % | Rubro 1 | Rubro 2
     */
    public function estadoAlumnosPorGrupo(int $idGrupo, Usuario $docente): array
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);

        if (!$grupo || $grupo->id_docente !== $docente->id_usuario) {
            return [];
        }

        $rubrosGrupo = $this->rubros->todosPorInstitucion($grupo->id_institucion);

        // Sesiones cerradas del grupo
        $sesiones   = $this->sesiones->todasPorGrupo($idGrupo)->where('est_sesion', 0);
        $sesionIds  = $sesiones->pluck('id_sesion')->toArray();
        $totalSes   = count($sesionIds);

        // Alumnos del grupo
        $vinculaciones = $this->grupoAlumnos->alumnosPorGrupo($idGrupo);

        return $vinculaciones->map(function ($ga) use ($sesionIds, $totalSes, $rubrosGrupo) {
            $alumno = $ga->alumno;
            if (!$alumno) return null;

            // Contar por estado
            $presentes    = 0;
            $ausentes     = 0;
            $justificadas = 0;

            foreach ($sesionIds as $idSesion) {
                $a = $this->asistencias->buscarPorSesionYAlumno($idSesion, $ga->id_alumno);
                if ($a) {
                    match ($a->est_asistencia) {
                        1 => $presentes++,
                        2 => $ausentes++,
                        3 => $justificadas++,
                        default => null,
                    };
                } else {
                    $ausentes++; // Sin registro = ausente
                }
            }

            // RF-69 — Porcentaje: (Presentes + Justificadas) / Total sesiones
            $cuentan      = $presentes + $justificadas;
            $porcentaje   = $totalSes > 0 ? round(($cuentan / $totalSes) * 100, 2) : 0.0;

            // RF-77 — Estado por cada rubro
            $estadoRubros = $rubrosGrupo->map(fn($r) => [
                'id_rubro'          => $r->id_rubro,
                'nombre'            => $r->nombre,
                'porcentaje_minimo' => (float) $r->porcentaje_minimo,
                'cumple'            => $porcentaje >= (float) $r->porcentaje_minimo,
            ])->values()->all();

            return [
                'id_usuario'   => $alumno->id_usuario,
                // RF-66 — Formato: Ap.Pat Ap.Mat, Nombre
                'nombre_completo' => "{$alumno->ap_pat} {$alumno->ap_mat}, {$alumno->nombre}",
                'asistencias'  => $presentes,
                'faltas'       => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje'   => $porcentaje,
                'rubros'       => $estadoRubros,
            ];
        })->filter()->values()->all();
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers privados
    // ─────────────────────────────────────────────────────────────

    /**
     * Calcula alumnos en riesgo en todos los grupos del docente.
     * Un alumno está en riesgo si no cumple con el porcentaje mínimo
     * de al menos un rubro de evaluación.
     */
    private function calcularAlumnosEnRiesgo(
        \Illuminate\Support\Collection $grupos,
        array $grupoIds
    ): array {
        $enRiesgo = [];

        foreach ($grupos as $grupo) {
            $rubros     = $this->rubros->todosPorInstitucion($grupo->id_institucion);
            if ($rubros->isEmpty()) continue;

            $sesiones   = $this->sesiones->todasPorGrupo($grupo->id_grupo)->where('est_sesion', 0);
            $sesionIds  = $sesiones->pluck('id_sesion')->toArray();
            $totalSes   = count($sesionIds);
            if ($totalSes === 0) continue;

            $vinculaciones = $this->grupoAlumnos->alumnosPorGrupo($grupo->id_grupo);

            foreach ($vinculaciones as $ga) {
                $presentes    = 0;
                $justificadas = 0;

                foreach ($sesionIds as $idSesion) {
                    $a = $this->asistencias->buscarPorSesionYAlumno($idSesion, $ga->id_alumno);
                    if ($a) {
                        if ($a->est_asistencia === 1) $presentes++;
                        if ($a->est_asistencia === 3) $justificadas++;
                    }
                }

                $porcentaje = round((($presentes + $justificadas) / $totalSes) * 100, 2);

                // Verificar si incumple algún rubro
                $rubroEnRiesgo = $rubros->first(
                    fn($r) => $porcentaje < (float) $r->porcentaje_minimo
                );

                if ($rubroEnRiesgo) {
                    $alumno = $ga->alumno;
                    if (!$alumno) continue;

                    $enRiesgo[] = [
                        'id_usuario'      => $alumno->id_usuario,
                        'nombre_completo' => "{$alumno->ap_pat} {$alumno->ap_mat}, {$alumno->nombre}",
                        'materia'         => $grupo->materia,
                        'nombre_grupo'    => $grupo->nombre,
                        'porcentaje'      => $porcentaje,
                        'rubro_riesgo'    => $rubroEnRiesgo->nombre,
                        'minimo_requerido'=> (float) $rubroEnRiesgo->porcentaje_minimo,
                    ];
                }
            }
        }

        return $enRiesgo;
    }

    private function resumenVacio(): array
    {
        return [
            'aulas_activas'            => 0,
            'sesiones_hoy'             => 0,
            'alumnos_en_riesgo'        => 0,
            'justificantes_pendientes' => 0,
            'sesiones_recientes'       => [],
            'alumnos_riesgo_detalle'   => [],
        ];
    }
}
