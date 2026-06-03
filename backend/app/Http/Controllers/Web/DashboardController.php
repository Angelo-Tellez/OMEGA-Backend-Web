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
use App\Services\DashboardWebService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GrupoRepositoryInterface      $grupos,
        private readonly InstitucionRepositoryInterface $instituciones,
        private readonly DashboardWebService            $dashboardWebService,
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
        $alumnosEnRiesgo = $this->dashboardWebService->calcularAlumnosEnRiesgo($gruposIds);

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

        $alumnosEnRiesgo = $this->dashboardWebService->calcularAlumnosEnRiesgo($gruposIds);

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

}
