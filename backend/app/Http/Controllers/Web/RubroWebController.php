<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/RubroWebController.php
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
use App\Models\RubroEvaluacion;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Services\RubroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controlador Web — Gestión de Rubros de Evaluación por Institución.
 * RF-04, RF-05 — Configurar porcentajes mínimos de asistencia por espacio.
 */
class RubroWebController extends Controller
{
    public function __construct(
        private readonly RubroService                  $rubros,
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    public function index(int $idInstitucion)
    {
        $institucion = $this->instituciones->buscarPorId($idInstitucion);

        if (!$institucion || $institucion->id_docente !== Auth::user()->id_usuario) {
            abort(403);
        }

        $rubros = RubroEvaluacion::where('id_institucion', $idInstitucion)
            ->orderBy('porcentaje_minimo', 'desc')
            ->get();

        return view('modules.rubros.index', compact('institucion', 'rubros'));
    }

    public function store(Request $request, int $idInstitucion)
    {
        $institucion = $this->instituciones->buscarPorId($idInstitucion);
        if (!$institucion || $institucion->id_docente !== Auth::user()->id_usuario) {
            abort(403);
        }

        try {
            $this->rubros->crear($idInstitucion, $request->all());
            return redirect()->route('ca.rubros.index', $idInstitucion)
                ->with('success', 'Rubro agregado correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function update(Request $request, int $idInstitucion, RubroEvaluacion $rubro)
    {
        $institucion = $this->instituciones->buscarPorId($idInstitucion);
        if (!$institucion || $institucion->id_docente !== Auth::user()->id_usuario) {
            abort(403);
        }

        try {
            $this->rubros->actualizar($rubro->id_rubro, $request->all());
            return redirect()->route('ca.rubros.index', $idInstitucion)
                ->with('success', 'Rubro actualizado correctamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(int $idInstitucion, RubroEvaluacion $rubro)
    {
        $institucion = $this->instituciones->buscarPorId($idInstitucion);
        if (!$institucion || $institucion->id_docente !== Auth::user()->id_usuario) {
            abort(403);
        }

        $rubro->delete();
        return redirect()->route('ca.rubros.index', $idInstitucion)
            ->with('success', 'Rubro eliminado correctamente');
    }
}
