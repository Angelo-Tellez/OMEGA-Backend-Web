<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/SesionController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Services\SesionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    public function __construct(
        private readonly SesionService $sesiones
    ) {}

    /**
     * RF-62 — Lista todas las sesiones de un grupo.
     * GET /api/grupos/{idGrupo}/sesiones
     */
    public function index(Request $request, int $idGrupo): JsonResponse
    {
        return response()->json([
            'data' => $this->sesiones->listar($idGrupo, $request->user()),
        ]);
    }

    /**
     * RF-63 — Consulta la sesión activa de un grupo.
     * GET /api/grupos/{idGrupo}/sesiones/activa
     * Retorna null en data si no hay sesión activa (Flutter lo usa para saber
     * si mostrar el botón "Abrir sesión" o la pantalla de control activa).
     */
    public function activa(Request $request, int $idGrupo): JsonResponse
    {
        $sesion = $this->sesiones->sesionActivaDelGrupo($idGrupo, $request->user());
        return response()->json(['data' => $sesion, 'message' => 'La información se cargó correctamente']);
    }

    /**
     * RF-62, RF-63 — Abre una nueva sesión y genera la clave única.
     * POST /api/grupos/{idGrupo}/sesiones/abrir
     */
    public function abrir(Request $request, int $idGrupo): JsonResponse
    {
        $sesion = $this->sesiones->abrir($idGrupo, $request->all(), $request->user());
        return response()->json(['data' => $sesion, 'message' => 'El registro se realizó correctamente'], 201);
    }

    /**
     * RF-64 — Cierra manualmente la sesión activa.
     * POST /api/sesiones/{sesion}/cerrar
     */
    public function cerrar(Request $request, Sesion $sesion): JsonResponse
    {
        $actualizada = $this->sesiones->cerrar($sesion, $request->user());
        return response()->json(['data' => $actualizada, 'message' => 'La información se actualizó correctamente']);
    }

    /**
     * RF-63 — Detalle de una sesión específica con estadísticas.
     * GET /api/sesiones/{sesion}
     */
    public function show(Request $request, Sesion $sesion): JsonResponse
    {
        return response()->json([
            'data' => $this->sesiones->obtener($sesion, $request->user()),
        ]);
    }
}
