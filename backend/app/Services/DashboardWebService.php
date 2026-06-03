<?php

/*
 * ============================================================
 * Servicio Web — Lógica de negocio del Dashboard del Docente.
 * MPL-OMEGA-05 §2.3 | §6.1
 * @version 1.0.0
 * ============================================================
 * Encapsula los cálculos de alumnos en riesgo para las
 * vistas Blade del panel principal del Docente.
 * Separado del DashboardService (API) para mantener SRP.
 * ============================================================
 */

namespace App\Services;

use App\Models\Asistencia;
use App\Models\GrupoAlumno;
use App\Models\RubroEvaluacion;
use App\Models\Sesion;
use Illuminate\Support\Collection;

class DashboardWebService
{
    /**
     * Calcula la colección de alumnos en riesgo para las vistas Blade.
     * Un alumno entra en riesgo si:
     *  - Ya excedió las faltas permitidas (perdio = true), o
     *  - Está a 5% o menos sobre el mínimo del rubro principal, o
     *  - Si hubiera una falta más en la siguiente sesión, perdería el derecho.
     *
     * @param  \Illuminate\Support\Collection $gruposIds IDs de grupos del docente
     * @return \Illuminate\Support\Collection
     */
    public function calcularAlumnosEnRiesgo(Collection $gruposIds): Collection
    {
        $alumnosEnRiesgo = collect();

        foreach ($gruposIds as $idGrupo) {
            // Rubros ordenados descendente (más exigente primero)
            $rubros = RubroEvaluacion::whereHas(
                'institucion.grupos',
                fn($q) => $q->where('id_grupo', $idGrupo)
            )->orderByDesc('porcentaje_minimo')->get();

            $rubroPrincipal  = $rubros->first();
            $pctPrincipal    = $rubroPrincipal?->porcentaje_minimo ?? 80;
            $nombrePrincipal = $rubroPrincipal?->nombre ?? 'Ordinario';

            $sesiones  = Sesion::where('id_grupo', $idGrupo)->where('est_sesion', 0)->get();
            $hayActiva = Sesion::where('id_grupo', $idGrupo)->where('est_sesion', 1)->exists();
            $totalSes  = $sesiones->count();

            if ($totalSes === 0) continue;

            $faltasPermitidas = (int) floor($totalSes * (1 - $pctPrincipal / 100));
            $alumnos          = GrupoAlumno::where('id_grupo', $idGrupo)
                ->with(['alumno', 'grupo'])->get();

            foreach ($alumnos as $ga) {
                $sesIds       = $sesiones->pluck('id_sesion');
                $presentes    = Asistencia::whereIn('id_sesion', $sesIds)->where('id_alumno', $ga->id_alumno)->where('est_asistencia', 1)->count();
                $justificadas = Asistencia::whereIn('id_sesion', $sesIds)->where('id_alumno', $ga->id_alumno)->where('est_asistencia', 3)->count();
                $ausentes     = Asistencia::whereIn('id_sesion', $sesIds)->where('id_alumno', $ga->id_alumno)->where('est_asistencia', 2)->count();

                $asistidas       = $presentes + $justificadas;
                $pct             = round(($asistidas / $totalSes) * 100, 1);
                $perdio          = $ausentes > $faltasPermitidas;
                $faltasRestantes = max(0, $faltasPermitidas - $ausentes);

                // Riesgo proyectado: si la siguiente falta supera el límite
                $enRiesgoProyectado = false;
                if (!$perdio && $hayActiva) {
                    $totalProyectado   = $totalSes + 1;
                    $faltasPermProyect = (int) floor($totalProyectado * (1 - $pctPrincipal / 100));
                    $enRiesgoProyectado = ($ausentes + 1) > $faltasPermProyect;
                }

                // Solo aparece si está dentro del margen de riesgo (5% sobre el mínimo)
                if (!$perdio && $pct > ($pctPrincipal + 5.0) && !$enRiesgoProyectado) continue;

                if ($perdio || ($faltasRestantes <= 2 && $faltasPermitidas > 0) || $enRiesgoProyectado) {
                    $rubroConDerecho = $rubros->first(fn($r) => $pct >= (float) $r->porcentaje_minimo);

                    $alumnosEnRiesgo->push([
                        'alumno'               => $ga->alumno,
                        'grupo'                => $ga->grupo,
                        'porcentaje'           => $pct,
                        'total_faltas'         => $ausentes,
                        'faltas_restantes'     => $faltasRestantes,
                        'perdio'               => $perdio,
                        'rubro_principal'      => $nombrePrincipal,
                        'pct_principal'        => $pctPrincipal,
                        'id_institucion'       => $ga->grupo->id_institucion,
                        'rubros'               => $rubros->values(),
                        'idx_rubro_derecho'    => $rubroConDerecho
                            ? $rubros->search(fn($r) => $r->id_rubro === $rubroConDerecho->id_rubro)
                            : null,
                        'nombre_rubro_derecho' => $rubroConDerecho?->nombre,
                    ]);
                }
            }
        }

        return $alumnosEnRiesgo;
    }
}
