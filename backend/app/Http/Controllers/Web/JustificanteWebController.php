<?php

/*
 * ============================================================
 * Controlador Web — Gestión de Justificantes de Asistencia.
 * MPL-OMEGA-05 §6.1 | §6.2
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Repositories\Contracts\AsistenciaRepositoryInterface;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Services\AsistenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class JustificanteWebController extends Controller
{
    public function __construct(
        private readonly AsistenciaRepositoryInterface $asistencias,
        private readonly GrupoRepositoryInterface      $grupos,
        private readonly AsistenciaService             $asistenciaService,
    ) {}

    public function index(Request $request)
    {
        $institucionId = session('institucion_id');

        if (!$institucionId) {
            return redirect()->route('ca.instituciones.index')
                ->with('info', 'Selecciona una institución para ver sus justificantes');
        }

        // Filtros
        $filtroPeriodo  = $request->query('periodo', '');
        $filtroDesde    = $request->query('desde', '');
        $filtroHasta    = $request->query('hasta', '');

        // Validar rango de fechas
        $errorFecha = null;
        if ($filtroDesde && $filtroHasta && $filtroDesde > $filtroHasta) {
            $errorFecha = 'La fecha inicial no puede ser posterior a la fecha final.';
            $filtroDesde = '';
            $filtroHasta = '';
        }

        $grupos = $this->grupos
            ->todosPorInstitucion($institucionId, Auth::user()->id_usuario)
            ->load([
                'sesiones' => function($q) use ($filtroDesde, $filtroHasta) {
                    $q->where('est_sesion', 0)->orderByDesc('fec_sesion');
                    if ($filtroDesde) $q->whereDate('fec_sesion', '>=', $filtroDesde);
                    if ($filtroHasta) $q->whereDate('fec_sesion', '<=', $filtroHasta);
                },
                'sesiones.asistencias' => fn($q) => $q->whereIn('est_asistencia', [2, 3]),
                'sesiones.asistencias.alumno',
            ]);

        // Filtrar por periodo
        if ($filtroPeriodo) {
            $grupos = $grupos->filter(fn($g) => $g->periodo === $filtroPeriodo);
        }

        // Periodos únicos para el select
        $periodos = $this->grupos
            ->todosPorInstitucion($institucionId, Auth::user()->id_usuario)
            ->pluck('periodo')->unique()->filter()->sort()->values();

        $todosGrupos = $this->grupos->todosPorInstitucion($institucionId, Auth::user()->id_usuario);

        return view('modules.justificantes.index', compact(
            'grupos', 'periodos', 'todosGrupos',
            'filtroPeriodo', 'filtroDesde', 'filtroHasta', 'errorFecha'
        ));
    }

    public function indexJson(Request $request)
    {
        $institucionId = session('institucion_id');
        if (!$institucionId) return response()->json([]);

        $filtroPeriodo = $request->query('periodo', '');
        $filtroGrupo   = $request->query('grupo', '');
        $filtroDesde   = $request->query('desde', '');
        $filtroHasta   = $request->query('hasta', '');

        if ($filtroDesde && $filtroHasta && $filtroDesde > $filtroHasta) {
            $filtroDesde = '';
            $filtroHasta = '';
        }

        $grupos = $this->grupos
            ->todosPorInstitucion($institucionId, Auth::user()->id_usuario)
            ->load([
                'sesiones' => function($q) use ($filtroDesde, $filtroHasta) {
                    $q->where('est_sesion', 0)->orderByDesc('fec_sesion');
                    if ($filtroDesde) $q->whereDate('fec_sesion', '>=', $filtroDesde);
                    if ($filtroHasta) $q->whereDate('fec_sesion', '<=', $filtroHasta);
                },
                'sesiones.asistencias' => fn($q) => $q->whereIn('est_asistencia', [2, 3]),
                'sesiones.asistencias.alumno',
            ]);

        if ($filtroPeriodo) $grupos = $grupos->filter(fn($g) => $g->periodo === $filtroPeriodo);
        if ($filtroGrupo)   $grupos = $grupos->filter(fn($g) => $g->id_grupo == $filtroGrupo);

        $resultado = $grupos->map(function ($grupo) {
            $sesiones = $grupo->sesiones->filter(fn($s) => $s->asistencias->count() > 0);
            if ($sesiones->isEmpty()) return null;

            return [
                'id'      => $grupo->id_grupo,
                'nombre'  => $grupo->nombre,
                'materia' => $grupo->materia,
                'periodo' => $grupo->periodo,
                'sesiones' => $sesiones->map(function ($sesion) {
                    return [
                        'id'           => $sesion->id_sesion,
                        'fecha'        => $sesion->fec_sesion->format('d/m/Y'),
                        'dia'          => ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'][$sesion->fec_sesion->dayOfWeek === 0 ? 6 : $sesion->fec_sesion->dayOfWeek - 1],
                        'hora_apertura' => $sesion->hora_apertura->format('H:i'),
                        'hora_cierre'   => $sesion->hora_cierre?->format('H:i'),
                        'ausentes'      => $sesion->asistencias->where('est_asistencia', 2)->count(),
                        'justificadas'  => $sesion->asistencias->where('est_asistencia', 3)->count(),
                        'alumnos' => $sesion->asistencias->map(function ($a) use ($sesion) {
                            return [
                                'id'            => $a->id_asistencia,
                                'nombre'        => $a->alumno?->ap_pat . ' ' . $a->alumno?->ap_mat . ', ' . $a->alumno?->nombre,
                                'email'         => $a->alumno?->email,
                                'estado'        => $a->est_asistencia,
                                'url_justificar' => route('ca.justificantes.justificar', $a->id_asistencia),
                                'url_ausente'    => route('ca.justificantes.ausente',    $a->id_asistencia),
                                'cargando'       => false,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->filter()->values();

        return response()->json($resultado);
    }

    public function justificar(Request $request, Asistencia $asistencia)
    {
        try {
            $this->asistenciaService->editarEstado($asistencia, ['est_asistencia' => 3]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'nuevo_estado' => 3]);
            }
            return redirect()->route('ca.justificantes.index')
                ->with('success', 'Asistencia justificada correctamente');
        } catch (ValidationException $e) {
            if ($request->ajax()) return response()->json(['ok' => false], 422);
            return back()->withErrors($e->errors());
        }
    }

    public function marcarAusente(Request $request, Asistencia $asistencia)
    {
        try {
            $this->asistenciaService->editarEstado($asistencia, ['est_asistencia' => 2]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'nuevo_estado' => 2]);
            }
            return redirect()->route('ca.justificantes.index')
                ->with('success', 'Asistencia revertida a ausente');
        } catch (ValidationException $e) {
            if ($request->ajax()) return response()->json(['ok' => false], 422);
            return back()->withErrors($e->errors());
        }
    }
}
