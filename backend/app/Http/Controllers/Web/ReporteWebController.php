<?php

namespace App\Http\Controllers\Web;

use App\Exports\ReporteGrupoExport;
use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\GrupoAlumno;
use App\Models\Sesion;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controlador Web — Reportes de asistencia por grupo.
 * RF-06 — Exportar Excel y PDF (plan mensual).
 * @version 1.1.0
 */
class ReporteWebController extends Controller
{
    public function __construct(
        private readonly GrupoRepositoryInterface $grupos,
    ) {}

    public function index(Request $request)
    {
        $institucionId = session('institucion_id');
        if (!$institucionId) {
            return redirect()->route('ca.instituciones.index')
                ->with('info', 'Selecciona una institución para ver sus reportes');
        }
        $grupos = $this->grupos->todosPorInstitucion($institucionId, Auth::user()->id_usuario);

        // Filtros
        $busqueda = $request->query('busqueda', '');
        $periodo  = $request->query('periodo', '');
        $minPct   = $request->query('min_pct', '');
        $maxPct   = $request->query('max_pct', '');

        $periodos = $grupos->pluck('periodo')->unique()->sort()->values();

        $reportes = $grupos->map(function ($grupo) {
            $sesiones  = Sesion::where('id_grupo', $grupo->id_grupo)->get();
            $sesionIds = $sesiones->pluck('id_sesion');

            $totalSesiones  = $sesiones->count();
            $totalPresentes = Asistencia::whereIn('id_sesion', $sesionIds)->where('est_asistencia', 1)->count();
            $totalAusentes  = Asistencia::whereIn('id_sesion', $sesionIds)->where('est_asistencia', 2)->count();
            $totalJustif    = Asistencia::whereIn('id_sesion', $sesionIds)->where('est_asistencia', 3)->count();
            $totalAsist     = $totalPresentes + $totalAusentes + $totalJustif;

            return [
                'grupo'           => $grupo,
                'total_sesiones'  => $totalSesiones,
                'total_presentes' => $totalPresentes,
                'total_ausentes'  => $totalAusentes,
                'total_justif'    => $totalJustif,
                'porcentaje'      => $totalAsist > 0
                    ? round(($totalPresentes / $totalAsist) * 100, 1)
                    : 0,
            ];
        });

        if ($busqueda) {
            $reportes = $reportes->filter(fn($r) =>
                str_contains(strtolower($r['grupo']->nombre), strtolower($busqueda)) ||
                str_contains(strtolower($r['grupo']->materia), strtolower($busqueda))
            );
        }
        if ($periodo) {
            $reportes = $reportes->filter(fn($r) => $r['grupo']->periodo === $periodo);
        }
        if ($minPct !== '') {
            $reportes = $reportes->filter(fn($r) => $r['porcentaje'] >= (float) $minPct);
        }
        if ($maxPct !== '') {
            $reportes = $reportes->filter(fn($r) => $r['porcentaje'] <= (float) $maxPct);
        }

        $grupos = $this->grupos->todosPorInstitucion($institucionId, Auth::user()->id_usuario);
        return view('modules.reportes.index', compact(
            'reportes', 'periodos', 'grupos',
            'busqueda', 'periodo', 'minPct', 'maxPct'
        ));
    }

    public function indexJson(Request $request)
    {
        // Reutiliza la misma lógica pero devuelve JSON para AJAX
        $institucionId = session('institucion_id');
        if (!$institucionId) return response()->json([]);

        $grupos   = $this->grupos->todosPorInstitucion($institucionId, Auth::user()->id_usuario);
        $busqueda = $request->query('busqueda', '');
        $periodo  = $request->query('periodo', '');
        $minPct   = $request->query('min_pct', '');
        $maxPct   = $request->query('max_pct', '');

        $reportes = $grupos->map(function ($grupo) {
            $sesiones  = Sesion::where('id_grupo', $grupo->id_grupo)->get();
            $sesionIds = $sesiones->pluck('id_sesion');
            $tp = Asistencia::whereIn('id_sesion', $sesionIds)->where('est_asistencia', 1)->count();
            $ta = Asistencia::whereIn('id_sesion', $sesionIds)->where('est_asistencia', 2)->count();
            $tj = Asistencia::whereIn('id_sesion', $sesionIds)->where('est_asistencia', 3)->count();
            $tt = $tp + $ta + $tj;
            return [
                'id'             => $grupo->id_grupo,
                'nombre'         => $grupo->nombre,
                'materia'        => $grupo->materia,
                'periodo'        => $grupo->periodo,
                'total_sesiones' => $sesiones->count(),
                'presentes'      => $tp,
                'ausentes'       => $ta,
                'justif'         => $tj,
                'porcentaje'     => $tt > 0 ? round(($tp / $tt) * 100, 1) : 0,
                'url_detalle'    => route('ca.reportes.detalle', $grupo->id_grupo),
            ];
        });

        if ($busqueda) $reportes = $reportes->filter(fn($r) => strtolower($r['nombre']) === strtolower($busqueda) || str_contains(strtolower($r['nombre'].$r['materia']), strtolower($busqueda)));
        $grupo = $request->query('grupo', '');
        if ($grupo) $reportes = $reportes->filter(fn($r) => strtolower($r['nombre']) === strtolower($grupo));
        if ($periodo)  $reportes = $reportes->filter(fn($r) => $r['periodo'] === $periodo);
        if ($minPct !== '') $reportes = $reportes->filter(fn($r) => $r['porcentaje'] >= (float)$minPct);
        if ($maxPct !== '') $reportes = $reportes->filter(fn($r) => $r['porcentaje'] <= (float)$maxPct);

        return response()->json($reportes->values());
    }

    public function detalle(Request $request, int $idGrupo)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $orden = $request->query('orden', 'asc'); // asc = primera sesión primero

        $sesiones = Sesion::where('id_grupo', $idGrupo)
            ->orderBy('fec_sesion', $orden === 'desc' ? 'desc' : 'asc')
            ->get()
            ->map(function ($sesion, $i) {
                return [
                    'sesion'    => $sesion,
                    'presentes' => Asistencia::where('id_sesion', $sesion->id_sesion)->where('est_asistencia', 1)->count(),
                    'ausentes'  => Asistencia::where('id_sesion', $sesion->id_sesion)->where('est_asistencia', 2)->count(),
                    'justif'    => Asistencia::where('id_sesion', $sesion->id_sesion)->where('est_asistencia', 3)->count(),
                    'num'       => $i + 1,
                ];
            });

        return view('modules.reportes.detalle', compact('grupo', 'sesiones', 'orden'));
    }

    /**
     * RF-06 — Exportar reporte de asistencia por alumno en Excel.
     */
    public function sesionesJson(Request $request, int $idGrupo)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $orden = $request->query('orden', 'asc');

        $sesiones = Sesion::where('id_grupo', $idGrupo)
            ->orderBy('fec_sesion', $orden === 'desc' ? 'desc' : 'asc')
            ->get()
            ->map(function ($sesion, $i) {
                return [
                    'num'       => $i + 1,
                    'fecha'     => $sesion->fec_sesion->format('d/m/Y'),
                    'hora_a'    => $sesion->hora_apertura->format('H:i'),
                    'hora_c'    => $sesion->hora_cierre?->format('H:i'),
                    'activa'    => $sesion->est_sesion === 1,
                    'presentes' => Asistencia::where('id_sesion', $sesion->id_sesion)->where('est_asistencia', 1)->count(),
                    'ausentes'  => Asistencia::where('id_sesion', $sesion->id_sesion)->where('est_asistencia', 2)->count(),
                    'justif'    => Asistencia::where('id_sesion', $sesion->id_sesion)->where('est_asistencia', 3)->count(),
                    'url'       => route('ca.sesiones.asistencias', $sesion->id_sesion),
                ];
            });

        return response()->json($sesiones->values());
    }

    public function alumnosJson(Request $request, int $idGrupo)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $busqueda  = $request->query('nombre', '');
        $ordenarPor = $request->query('ordenar', ''); // 'asistencias', 'faltas', 'justificaciones'
        $dirOrden  = $request->query('dir', 'desc');

        $sesionesIds = Sesion::where('id_grupo', $idGrupo)->where('est_sesion', 0)->pluck('id_sesion');
        $total = $sesionesIds->count();

        $alumnos = GrupoAlumno::where('id_grupo', $idGrupo)->with('alumno')->get()
            ->filter(fn($ga) => $ga->alumno !== null)
            ->map(function ($ga) use ($sesionesIds, $total, $idGrupo) {
                $al = $ga->alumno;
                $p  = Asistencia::whereIn('id_sesion', $sesionesIds)->where('id_alumno', $al->id_usuario)->where('est_asistencia', 1)->count();
                $a  = Asistencia::whereIn('id_sesion', $sesionesIds)->where('id_alumno', $al->id_usuario)->where('est_asistencia', 2)->count();
                $j  = Asistencia::whereIn('id_sesion', $sesionesIds)->where('id_alumno', $al->id_usuario)->where('est_asistencia', 3)->count();
                $pct = $total > 0 ? round((($p + $j) / $total) * 100, 1) : 0;
                return [
                    'id'     => $al->id_usuario,
                    'nombre' => trim($al->ap_pat . ' ' . $al->ap_mat . ', ' . $al->nombre),
                    'email'  => $al->email ?? '',
                    'p'      => (int) $p,
                    'a'      => (int) $a,
                    'j'      => (int) $j,
                    'pct'    => (float) $pct,
                    'url'    => route('ca.reportes.alumno', [$idGrupo, $al->id_usuario]),
                ];
            });

        if ($busqueda) {
            $alumnos = $alumnos->filter(fn($al) => str_contains(strtolower($al['nombre']), strtolower($busqueda)));
        }

        if ($ordenarPor === 'asistencias') {
            $alumnos = $dirOrden === 'asc' ? $alumnos->sortBy('p') : $alumnos->sortByDesc('p');
        } elseif ($ordenarPor === 'faltas') {
            $alumnos = $dirOrden === 'asc' ? $alumnos->sortBy('a') : $alumnos->sortByDesc('a');
        } elseif ($ordenarPor === 'justificaciones') {
            $alumnos = $dirOrden === 'asc' ? $alumnos->sortBy('j') : $alumnos->sortByDesc('j');
        } else {
            $alumnos = $alumnos->sortBy('nombre');
        }

        return response()->json($alumnos->values());
    }

    public function detalleAlumnoJson(Request $request, int $idGrupo, int $idAlumno)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $filtroDesde  = $request->query('desde', '');
        $filtroHasta  = $request->query('hasta', '');
        $filtroEstado = $request->query('estado', '');

        $query = \App\Models\Sesion::where('id_grupo', $idGrupo)->orderBy('fec_sesion');
        if ($filtroDesde) $query->whereDate('fec_sesion', '>=', $filtroDesde);
        if ($filtroHasta) $query->whereDate('fec_sesion', '<=', $filtroHasta);

        $sesiones = $query->get()->map(function ($sesion, $i) use ($idAlumno) {
            $asistencia = \App\Models\Asistencia::where('id_sesion', $sesion->id_sesion)
                ->where('id_alumno', $idAlumno)->first();
            return [
                'num'    => $i + 1,
                'fecha'  => $sesion->fec_sesion->format('d/m/Y'),
                'hora'   => $sesion->hora_apertura->format('H:i'),
                'estado' => $asistencia?->est_asistencia ?? null,
                'hora_registro' => $asistencia?->hora_registro?->format('H:i') ?? '—',
            ];
        });

        if ($filtroEstado !== '') {
            $sesiones = $sesiones->filter(fn($s) => $s['estado'] == (int)$filtroEstado)->values();
        }

        $p = $sesiones->where('estado', 1)->count();
        $a = $sesiones->where('estado', 2)->count();
        $j = $sesiones->where('estado', 3)->count();
        $t = $sesiones->count();

        return response()->json([
            'sesiones'     => $sesiones->values(),
            'presentes'    => $p,
            'ausentes'     => $a,
            'justificadas' => $j,
            'total'        => $t,
            'porcentaje'   => $t > 0 ? round((($p + $j) / $t) * 100, 1) : 0,
        ]);
    }

    public function exportarExcel(int $idGrupo)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $nombre = 'reporte-' . $grupo->nombre . '-' . $grupo->materia . '.xlsx';
        return Excel::download(new ReporteGrupoExport($grupo), $nombre);
    }

    /**
     * RF-06 — Exportar reporte de asistencia por alumno en PDF.
     */
    public function exportarPdf(int $idGrupo)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $sesiones = Sesion::where('id_grupo', $idGrupo)
            ->where('est_sesion', 0)
            ->orderBy('fec_sesion')
            ->get();

        $alumnos = GrupoAlumno::where('id_grupo', $idGrupo)
            ->with('alumno')
            ->get()
            ->map(function ($ga) use ($sesiones) {
                $alumno    = $ga->alumno;
                $presentes = 0;
                $ausentes  = 0;
                $justif    = 0;

                foreach ($sesiones as $sesion) {
                    $asistencia = Asistencia::where('id_sesion', $sesion->id_sesion)
                        ->where('id_alumno', $alumno->id_usuario)
                        ->first();
                    if ($asistencia) {
                        match ($asistencia->est_asistencia) {
                            1 => $presentes++,
                            2 => $ausentes++,
                            3 => $justif++,
                            default => null,
                        };
                    }
                }

                $total = $sesiones->count();
                return [
                    'alumno'     => $alumno,
                    'presentes'  => $presentes,
                    'ausentes'   => $ausentes,
                    'justif'     => $justif,
                    'total'      => $total,
                    'porcentaje' => $total > 0 ? round((($presentes + $justif) / $total) * 100, 1) : 0,
                ];
            })
            ->sortBy('alumno.ap_pat')
            ->values();

        $pdf = Pdf::loadView('modules.reportes.pdf', compact('grupo', 'sesiones', 'alumnos'))
                  ->setPaper('letter', 'landscape');

        return $pdf->download('reporte-' . $grupo->nombre . '-' . $grupo->materia . '.pdf');
    }
    /**
     * Detalle de asistencia sesión a sesión de un alumno en un grupo.
     */
    public function detalleAlumno(Request $request, int $idGrupo, int $idAlumno)
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);
        abort_if(!$grupo || $grupo->id_docente !== Auth::user()->id_usuario, 403);

        $alumno = \App\Models\Usuario::findOrFail($idAlumno);

        $filtroDesde  = $request->query('desde', '');
        $filtroHasta  = $request->query('hasta', '');
        $filtroEstado = $request->query('estado', '');

        $errorFecha = null;
        if ($filtroDesde && $filtroHasta && $filtroDesde > $filtroHasta) {
            $errorFecha   = 'La fecha inicial no puede ser posterior a la fecha final.';
            $filtroDesde  = '';
            $filtroHasta  = '';
        }

        $query = \App\Models\Sesion::where('id_grupo', $idGrupo)->orderBy('fec_sesion');
        if ($filtroDesde) $query->whereDate('fec_sesion', '>=', $filtroDesde);
        if ($filtroHasta) $query->whereDate('fec_sesion', '<=', $filtroHasta);

        $sesiones = $query->get()->map(function ($sesion) use ($idAlumno) {
            $asistencia = \App\Models\Asistencia::where('id_sesion', $sesion->id_sesion)
                ->where('id_alumno', $idAlumno)->first();
            return [
                'sesion'     => $sesion,
                'asistencia' => $asistencia,
                'estado'     => $asistencia?->est_asistencia ?? null,
                'hora'       => $asistencia?->hora_registro?->format('H:i') ?? '—',
            ];
        });

        if ($filtroEstado !== '') {
            $sesiones = $sesiones->filter(fn($s) => $s['estado'] == (int) $filtroEstado);
        }

        $presentes    = $sesiones->where('estado', 1)->count();
        $ausentes     = $sesiones->where('estado', 2)->count();
        $justificadas = $sesiones->where('estado', 3)->count();
        $total        = $sesiones->count();
        $porcentaje   = $total > 0 ? round((($presentes + $justificadas) / $total) * 100, 1) : 0;

        return view('modules.reportes.detalle_alumno', compact(
            'grupo', 'alumno', 'sesiones',
            'presentes', 'ausentes', 'justificadas', 'total', 'porcentaje',
            'filtroDesde', 'filtroHasta', 'filtroEstado', 'errorFecha'
        ));
    }
}