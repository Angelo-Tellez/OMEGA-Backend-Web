<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/RubroEvaluacionController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Models\RubroEvaluacion;
use App\Services\RubroEvaluacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Gestión de rubros de evaluación por institución.
 * Sin lógica de negocio, solo delega al RubroEvaluacionService.
 */
class RubroEvaluacionController extends Controller
{
    public function __construct(
        private readonly RubroEvaluacionService $rubros
    ) {}

    public function index(Request $request, int $idInstitucion): JsonResponse
    {
        return response()->json([
            'data' => $this->rubros->listar($idInstitucion, $request->user()),
        ]);
    }

    public function store(Request $request, int $idInstitucion): JsonResponse
    {
        $creado = $this->rubros->crear($idInstitucion, $request->all(), $request->user());
        return response()->json(['data' => $creado, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function update(Request $request, RubroEvaluacion $rubroEvaluacion): JsonResponse
    {
        $actualizado = $this->rubros->actualizar($rubroEvaluacion, $request->all(), $request->user());
        return response()->json(['data' => $actualizado, 'message' => 'La información se actualizó correctamente']);
    }

    public function destroy(Request $request, RubroEvaluacion $rubroEvaluacion): JsonResponse
    {
        $this->rubros->eliminar($rubroEvaluacion, $request->user());
        return response()->noContent();
    }
}