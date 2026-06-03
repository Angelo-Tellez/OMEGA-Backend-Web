<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/SesionWebController.php
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
use App\Models\Sesion;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\SesionRepositoryInterface;
use App\Services\SesionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controlador Web — Gestión de Sesiones de Asistencia.
 * @version 1.0.0
 */
class SesionWebController extends Controller
{
    public function __construct(
        private readonly SesionService            $sesiones,
        private readonly SesionRepositoryInterface $repo,
        private readonly GrupoRepositoryInterface  $grupos,
    ) {}

    public function index(Grupo $grupo)
    {
        $sesiones = $this->repo->todasPorGrupo($grupo->id_grupo);
        return view('modules.sesiones.index', compact('grupo', 'sesiones'));
    }

    public function abrir(Request $request, Grupo $grupo)
    {
        try {
            $this->sesiones->abrir($grupo->id_grupo, [
                'fec_sesion' => now()->toDateString(),
            ], Auth::user());

            return redirect()->route('ca.grupos.sesiones', $grupo->id_grupo)
                ->with('success', 'La información se registró correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function cerrar(Sesion $sesion)
    {
        try {
            $this->sesiones->cerrar($sesion, Auth::user());
            return redirect()->back()
                ->with('success', 'La información se actualizó correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function asistencias(Sesion $sesion)
    {
        $sesion->load('grupo');
        $asistencias = $sesion->asistencias()->with('alumno')->get();
        return view('modules.sesiones.asistencias', compact('sesion', 'asistencias'));
    }
}