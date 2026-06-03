<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/InstitucionWebController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Sesion;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Services\InstitucionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controlador Web — Gestión de Instituciones del Docente.
 * @version 1.1.0
 */
class InstitucionWebController extends Controller
{
    public function __construct(
        private readonly InstitucionService             $instituciones,
        private readonly InstitucionRepositoryInterface $repo,
        private readonly GrupoRepositoryInterface       $grupos,
    ) {}

    public function index()
    {
        $docente       = Auth::user();
        $instituciones = $this->repo->todasPorDocente($docente->id_usuario)
            ->map(function ($inst) {
                $grupos = $this->grupos->todosPorInstitucion($inst->id_institucion)
                    ->map(function ($grupo) {
                        $sesionActiva = Sesion::where('id_grupo', $grupo->id_grupo)
                            ->where('est_sesion', 1)->first();
                        return [
                            'grupo'        => $grupo,
                            'sesionActiva' => $sesionActiva,
                            'totalAlumnos' => $grupo->grupoAlumnos()->count(),
                        ];
                    });
                return ['institucion' => $inst, 'grupos' => $grupos];
            });

        return view('modules.instituciones.index', compact('instituciones'));
    }

    public function create()
    {
        return view('modules.instituciones.create');
    }

    public function store(Request $request)
    {
        try {
            $institucion = $this->instituciones->crearModelo($request->all(), Auth::user());

            // Crear rubros
            if ($request->has('rubros')) {
                foreach ($request->rubros as $rubro) {
                    if (!empty($rubro['nombre'])) {
                        \App\Models\RubroEvaluacion::create([
                            'id_institucion'   => $institucion->id_institucion,
                            'nombre'           => $rubro['nombre'],
                            'porcentaje_minimo' => (int) $rubro['porcentaje'],
                        ]);
                    }
                }
            }

            // Crear periodos
            if ($request->has('periodos')) {
                foreach ($request->periodos as $periodo) {
                    if (!empty($periodo)) {
                        \App\Models\Periodo::create([
                            'id_institucion' => $institucion->id_institucion,
                            'nombre'         => $periodo,
                            'activo'         => true,
                        ]);
                    }
                }
            }

            return redirect()->route('ca.instituciones.index')
                ->with('success', 'Institución creada correctamente con sus rubros y periodos');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function edit(Institucion $institucion)
    {
        $this->instituciones->obtener($institucion, Auth::user());
        return view('modules.instituciones.edit', compact('institucion'));
    }

    public function update(Request $request, Institucion $institucion)
    {
        try {
            $this->instituciones->actualizar($institucion, $request->all(), Auth::user());
            return redirect()->route('ca.instituciones.index')
                ->with('success', 'La información se actualizó correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Institucion $institucion)
    {
        $this->instituciones->eliminar($institucion, Auth::user());

        // Si la institución eliminada era la activa, limpiar la sesión
        if (session('institucion_id') == $institucion->id_institucion) {
            session()->forget(['institucion_id', 'institucion_nombre']);
        }

        return redirect()->route('ca.instituciones.index')
            ->with('success', 'El registro se eliminó correctamente');
    }

    /**
     * Guarda la institución activa en sesión y redirige a Mis Aulas.
     */
    public function seleccionar(int $id, Request $request)
    {
        $institucion = $this->repo->buscarPorId($id);

        if (!$institucion || $institucion->id_docente !== Auth::user()->id_usuario) {
            abort(403);
        }

        session([
            'institucion_id'     => $institucion->id_institucion,
            'institucion_nombre' => $institucion->nombre,
        ]);

        // Si es AJAX, solo confirmar — no redirigir
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok'     => true,
                'nombre' => $institucion->nombre,
                'id'     => $institucion->id_institucion,
            ]);
        }

        return redirect()->route('ca.grupos.index');
    }

    /**
     * Selecciona la institución y redirige a una URL específica.
     * Usado desde el dashboard para ir directo a sesiones/alumnos de un grupo.
     */
    public function seleccionarYRedirigir(int $id, Request $request)
    {
        $institucion = $this->repo->buscarPorId($id);

        if (!$institucion || $institucion->id_docente !== Auth::user()->id_usuario) {
            abort(403);
        }

        session([
            'institucion_id'     => $institucion->id_institucion,
            'institucion_nombre' => $institucion->nombre,
        ]);

        $destino = $request->query('destino', route('ca.grupos.index'));
        return redirect($destino);
    }
}
