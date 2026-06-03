<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/DashboardController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard
    ) {}

    /**
     * RF-76 — Resumen del dashboard: tarjetas + sesiones recientes + alumnos en riesgo.
     * GET /api/dashboard
     */
    public function resumen(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboard->resumen($request->user()),
        ]);
    }

    /**
     * RF-77 — Estado de alumnos vs rubros de evaluación de un grupo.
     * GET /api/grupos/{idGrupo}/reporte-alumnos
     */
    public function estadoAlumnos(Request $request, int $idGrupo): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboard->estadoAlumnosPorGrupo($idGrupo, $request->user()),
        ]);
    }
}
