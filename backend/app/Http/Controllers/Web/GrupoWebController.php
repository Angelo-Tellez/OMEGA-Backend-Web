<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/GrupoWebController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoAlumno;
use Illuminate\Http\Request;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Services\GrupoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controlador Web — Gestión de Grupos/Aulas del Docente.
 * @version 1.1.0
 */
class GrupoWebController extends Controller
{
    public function __construct(
        private readonly GrupoService                  $grupos,
        private readonly GrupoRepositoryInterface      $repo,
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    public function index()
    {
        $institucionId = session('institucion_id');

        // Si no hay institución activa, redirigir a seleccionar una
        if (!$institucionId) {
            return redirect()->route('ca.instituciones.index')
                ->with('info', 'Selecciona una institución para ver sus aulas');
        }

        $grupos = $this->repo->todosPorInstitucion($institucionId);
        return view('modules.grupos.index', compact('grupos'));
    }

    public function create()
    {
        $instituciones = $this->instituciones->todasPorDocente(Auth::user()->id_usuario);
        $periodos = \App\Models\Periodo::where('id_institucion', session('institucion_id'))
            ->where('activo', true)->orderByDesc('created_at')->get();
        return view('modules.grupos.create', compact('instituciones', 'periodos'));
    }

    public function store(Request $request)
    {
        try {
            $this->grupos->crear($request->all(), Auth::user());
            return redirect()->route('ca.grupos.index')
                ->with('success', 'La información se registró correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function edit(Grupo $grupo)
    {
        $instituciones = $this->instituciones->todasPorDocente(Auth::user()->id_usuario);
        return view('modules.grupos.edit', compact('grupo', 'instituciones'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        try {
            $this->grupos->actualizar($grupo, $request->all(), Auth::user());
            return redirect()->route('ca.grupos.index')
                ->with('success', 'La información se actualizó correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Grupo $grupo)
    {
        $this->grupos->eliminar($grupo, Auth::user());
        return redirect()->route('ca.grupos.index')
            ->with('success', 'El registro se eliminó correctamente');
    }

    public function generarCodigo(Grupo $grupo)
    {
        $this->grupos->generarCodigoInv($grupo, Auth::user());
        return redirect()->route('ca.grupos.index')
            ->with('success', 'Código de invitación generado correctamente');
    }
    /**
     * RF-61 — Cerrar periodo académico: elimina sesiones y asistencias del grupo.
     * Requiere confirmación obligatoria. Conserva la estructura del grupo e institución.
     */
    public function cerrarPeriodo(Request $request, Grupo $grupo)
    {
        abort_if($grupo->id_docente !== Auth::user()->id_usuario, 403);

        // Eliminar asistencias
        \App\Models\Asistencia::whereHas('sesion', fn($q) => $q->where('id_grupo', $grupo->id_grupo))->delete();
        // Eliminar sesiones
        \App\Models\Sesion::where('id_grupo', $grupo->id_grupo)->delete();
        // Eliminar inscripciones
        \App\Models\GrupoAlumno::where('id_grupo', $grupo->id_grupo)->delete();
        // Eliminar el grupo completo
        $grupo->delete();

        return redirect()->route('ca.grupos.index')
            ->with('success', 'Grupo eliminado correctamente. Todos los datos han sido borrados.');
    }
}