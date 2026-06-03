<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/GrupoAlumnoWebController.php
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
use App\Repositories\Contracts\GrupoRepositoryInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador Web — Gestión de alumnos por grupo.
 * RF-12, RF-26, RF-27 — Ver y eliminar alumnos de un grupo.
 */
class GrupoAlumnoWebController extends Controller
{
    public function __construct(
        private readonly GrupoRepositoryInterface $grupos,
    ) {}

    public function index(Grupo $grupo)
    {
        abort_if($grupo->id_docente !== Auth::user()->id_usuario, 403);

        $alumnos = GrupoAlumno::where('id_grupo', $grupo->id_grupo)
            ->with('alumno')
            ->orderBy('fec_inscripcion')
            ->get();

        return view('modules.grupos.alumnos', compact('grupo', 'alumnos'));
    }

    public function destroy(Grupo $grupo, GrupoAlumno $grupoAlumno)
    {
        abort_if($grupo->id_docente !== Auth::user()->id_usuario, 403);
        abort_if($grupoAlumno->id_grupo !== $grupo->id_grupo, 403);

        $grupoAlumno->delete();

        return redirect()->route('ca.grupos.alumnos', $grupo)
            ->with('success', 'Alumno eliminado del grupo correctamente');
    }
}
