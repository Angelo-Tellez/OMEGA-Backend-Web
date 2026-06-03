<?php

/*
 * ============================================================
 * Controlador Web — Dashboard del Docente.
 * MPL-OMEGA-05 §6.1 | §6.2
 * @version 1.1.0
 * ============================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\GrupoAlumno;
use App\Models\RubroEvaluacion;
use App\Models\Sesion;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GrupoRepositoryInterface      $grupos,
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $docente   = Auth::user();
        $gruposAll = $this->grupos->todosPorDocente($docente->id_usuario);
        $gruposIds = $gruposAll->pluck('id_grupo');

        // Filtros GET (antes de calcular métricas para usarlos en las tarjetas)
        $filtroInst   = $request->query('inst', '');
        $filtroGrupo  = $request->query('grupo', '');
        $filtroEstado = $request->query('estado', '');

        // Subconjunto de grupos para las tarjetas de resumen
        if ($filtroGrupo) {
            $gruposIdsFiltrados = collect([$filtroGrupo]);
        } elseif ($filtroInst) {
            $gruposIdsFiltrados = $gruposAll
                ->filter(fn($g) => $g->id_institucion == $filtroInst)
                ->pluck('id_grupo');
        } else {
            $gruposIdsFiltrados = $gruposIds;
        }

        // Sesiones de hoy (filtradas)
        $sesionesHoy = Sesion::whereIn('id_grupo', $gruposIdsFiltrados)
            ->whereDate('fec_sesion', today())
            ->with('grupo')
            ->orderByDesc('hora_apertura')
            ->get()
            ->map(function ($sesion) {
                $presentes = Asistencia::where('id_sesion', $sesion->id_sesion)
                    ->where('est_asistencia', 1)->count();
                return [
                    'sesion'    => $sesion,
                    'presentes' => $presentes,
                    'total'     => $sesion->grupo->grupoAlumnos()->count(),
                ];
            });

        // RF-76: Alumnos en riesgo (global, para los selects y la sección completa)
        $alumnosEnRiesgo = $this->calcularAlumnosEnRiesgo($gruposIds);

        // Tarjetas de resumen (valores sincronizados con el filtro activo)
        $aulasActivas      = $gruposIdsFiltrados->count();
        $justificantesPend = Asistencia::whereHas('sesion', fn($q) => $q->whereIn('id_grupo', $gruposIdsFiltrados))
            ->where('est_asistencia', 2)->count();

        // Filtrar alumnos en riesgo según los tres filtros
        $alumnosFiltrados = $alumnosEnRiesgo;
        if ($filtroInst)   $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => $i['id_institucion'] == $filtroInst);
        if ($filtroGrupo)  $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => $i['grupo']->id_grupo == $filtroGrupo);
        if ($filtroEstado === 'excedido') $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => $i['perdio']);
        if ($filtroEstado === 'riesgo')   $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => !$i['perdio']);

        $riesgoPorGrupo      = $alumnosFiltrados->groupBy(fn($i) => $i['grupo']->id_grupo);
        $countRiesgoFiltrado = $alumnosFiltrados->count();

        // Instituciones para el select
        $instSelect = $this->instituciones->todasPorDocente($docente->id_usuario)
            ->map(fn($inst) => [
                'id'     => $inst->id_institucion,
                'nombre' => $inst->nombre,
            ])->values();

        // Grupos para el select (filtrados por institución)
        $gruposSelect = $alumnosEnRiesgo
            ->when($filtroInst, fn($c) => $c->filter(fn($i) => $i['id_institucion'] == $filtroInst))
            ->groupBy(fn($i) => $i['grupo']->id_grupo)
            ->map(fn($items, $grupoId) => [
                'id'     => $grupoId,
                'nombre' => $items->first()['grupo']->nombre . ' — ' . $items->first()['grupo']->materia,
            ])->values();

        $idInstLeyenda = $filtroInst
            ?: $alumnosEnRiesgo->first()['id_institucion'] ?? null;
        $rubrosLeyenda = $idInstLeyenda
            ? RubroEvaluacion::where('id_institucion', $idInstLeyenda)
                ->orderByDesc('porcentaje_minimo')->get()
            : collect();

        return view('modules.dashboard.index', compact(
            'sesionesHoy', 'aulasActivas',
            'justificantesPend', 'alumnosEnRiesgo', 'countRiesgoFiltrado',
            'riesgoPorGrupo', 'instSelect', 'gruposSelect',
            'filtroInst', 'filtroGrupo', 'filtroEstado',
            'rubrosLeyenda'
        ));
    }

    /**
     * Partial de alumnos en riesgo para AJAX (sin recargar la página).
     */
    public function riesgoPartial(\Illuminate\Http\Request $request)
    {
        $docente       = \Illuminate\Support\Facades\Auth::user();
        $gruposIds     = $this->grupos->todosPorDocente($docente->id_usuario)->pluck('id_grupo');
        $instituciones = $this->instituciones->todasPorDocente($docente->id_usuario);

        $alumnosEnRiesgo = $this->calcularAlumnosEnRiesgo($gruposIds);

        $filtroInst   = $request->query('inst', '');
        $filtroGrupo  = $request->query('grupo', '');
        $filtroEstado = $request->query('estado', '');

        $alumnosFiltrados = $alumnosEnRiesgo;
        if ($filtroInst)                 $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => $i['id_institucion'] == $filtroInst);
        if ($filtroGrupo)                $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => $i['grupo']->id_grupo == $filtroGrupo);
        if ($filtroEstado === 'excedido') $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => $i['perdio']);
        if ($filtroEstado === 'riesgo')   $alumnosFiltrados = $alumnosFiltrados->filter(fn($i) => !$i['perdio']);

        $riesgoPorGrupo = $alumnosFiltrados->groupBy(fn($i) => $i['grupo']->id_grupo);

        $instSelect = $instituciones->map(fn($inst) => [
            'id'     => $inst->id_institucion,
            'nombre' => $inst->nombre,
        ])->values();

        $gruposSelect = $alumnosEnRiesgo
            ->when($filtroInst, fn($c) => $c->filter(fn($i) => $i['id_institucion'] == $filtroInst))
            ->groupBy(fn($i) => $i['grupo']->id_grupo)
            ->map(fn($items, $grupoId) => [
                'id'     => (string) $grupoId,
                'nombre' => $items->first()['grupo']->nombre . ' — ' . $items->first()['grupo']->materia,
            ])->values();

        $idInstLeyenda = $filtroInst
            ?: $alumnosEnRiesgo->first()['id_institucion'] ?? null;
        $rubrosLeyenda = $idInstLeyenda
            ? RubroEvaluacion::where('id_institucion', $idInstLeyenda)
                ->orderByDesc('porcentaje_minimo')->get()
            : collect();

        $html = view('modules.dashboard.partials.riesgo', compact(
            'alumnosEnRiesgo', 'riesgoPorGrupo',
            'instSelect', 'gruposSelect',
            'filtroInst', 'filtroGrupo', 'filtroEstado',
            'rubrosLeyenda'
        ))->render();

        return response($html, 200)->header('Content-Type', 'text/html');
    }

    /**
     * Calcula los alumnos en riesgo para un conjunto de grupos.
     */
    private function calcularAlumnosEnRiesgo($gruposIds): \Illuminate\Support\Collection
    {
        $alumnosEnRiesgo = collect();
        foreach ($gruposIds as $idGrupo) {
            $rubros = RubroEvaluacion::whereHas('institucion.grupos', fn($q) => $q->where('id_grupo', $idGrupo))
                ->orderByDesc('porcentaje_minimo')->get();

            $rubroPrincipal  = $rubros->first();
            $pctPrincipal    = $rubroPrincipal?->porcentaje_minimo ?? 80;
            $nombrePrincipal = $rubroPrincipal?->nombre ?? 'Ordinario';

            $sesiones   = Sesion::where('id_grupo', $idGrupo)->where('est_sesion', 0)->get();
            $hayActiva  = Sesion::where('id_grupo', $idGrupo)->where('est_sesion', 1)->exists();
            $totalSes   = $sesiones->count();
            if ($totalSes === 0) continue;

            $faltasPermitidas = (int) floor($totalSes * (1 - $pctPrincipal / 100));

            $alumnos = GrupoAlumno::where('id_grupo', $idGrupo)->with(['alumno', 'grupo'])->get();
            foreach ($alumnos as $ga) {
                $sesIds       = $sesiones->pluck('id_sesion');
                $presentes    = Asistencia::whereIn('id_sesion', $sesIds)->where('id_alumno', $ga->id_alumno)->where('est_asistencia', 1)->count();
                $justificadas = Asistencia::whereIn('id_sesion', $sesIds)->where('id_alumno', $ga->id_alumno)->where('est_asistencia', 3)->count();
                $ausentes     = Asistencia::whereIn('id_sesion', $sesIds)->where('id_alumno', $ga->id_alumno)->where('est_asistencia', 2)->count();

                $asistidas       = $presentes + $justificadas;
                $pct             = round(($asistidas / $totalSes) * 100, 1);
                $perdio          = $ausentes > $faltasPermitidas;
                $faltasRestantes = max(0, $faltasPermitidas - $ausentes);

                $enRiesgoProyectado = false;
                if (!$perdio && $hayActiva) {
                    $totalProyectado    = $totalSes + 1;
                    $faltasPermProyect  = (int) floor($totalProyectado * (1 - $pctPrincipal / 100));
                    $enRiesgoProyectado = ($ausentes + 1) > $faltasPermProyect;
                }

                // Un alumno entra en riesgo solo si está a 5% o menos por encima del mínimo del rubro principal
                if (!$perdio && $pct > ($pctPrincipal + 5.0) && !$enRiesgoProyectado) continue;

                if ($perdio || ($faltasRestantes <= 2 && $faltasPermitidas > 0) || $enRiesgoProyectado) {
                    // Calcular a qué rubro tiene derecho según su porcentaje actual
                    // Los rubros vienen ordenados descendente (más exigente primero)
                    // Se busca el primer rubro cuyo mínimo el alumno SÍ cumple
                    $rubroConDerecho = $rubros->first(fn($r) => $pct >= (float) $r->porcentaje_minimo);

                    $alumnosEnRiesgo->push([
                        'alumno'             => $ga->alumno,
                        'grupo'              => $ga->grupo,
                        'porcentaje'         => $pct,
                        'total_faltas'       => $ausentes,
                        'faltas_restantes'   => $faltasRestantes,
                        'perdio'             => $perdio,
                        'rubro_principal'    => $nombrePrincipal,
                        'pct_principal'      => $pctPrincipal,
                        'id_institucion'     => $ga->grupo->id_institucion,
                        // Rubros ordenados desc para la vista (índice 0 = más exigente)
                        'rubros'             => $rubros->values(),
                        // Índice del rubro al que tiene derecho (null = sin derecho)
                        'idx_rubro_derecho'  => $rubroConDerecho
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