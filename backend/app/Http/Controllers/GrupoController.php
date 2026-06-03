<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/GrupoController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Services\GrupoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Gestión de grupos del Docente.
 * Sin lógica de negocio, solo delega al GrupoService.
 */
class GrupoController extends Controller
{
    public function __construct(
        private readonly GrupoService $grupos
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->grupos->listar($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $creado = $this->grupos->crear($request->all(), $request->user());
        return response()->json(['data' => $creado, 'message' => 'El registro se realizó correctamente'], 201);
    }

    /**
     * POST /api/instituciones/{idInstitucion}/grupos
     * Versión para la app móvil que recibe el id_institucion en la URL.
     */
    public function indexParaInstitucion(Request $request, int $idInstitucion): JsonResponse
    {
        $grupos = $this->grupos->listarPorInstitucion($idInstitucion, $request->user());
        return response()->json(['data' => $grupos, 'message' => 'La información se cargó correctamente']);
    }

    public function storeParaInstitucion(Request $request, int $idInstitucion): JsonResponse
    {
        $datos = array_merge($request->all(), ['id_institucion' => $idInstitucion]);
        $creado = $this->grupos->crear($datos, $request->user());
        return response()->json(['data' => $creado, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function show(Request $request, Grupo $grupo): JsonResponse
    {
        return response()->json([
            'data' => $this->grupos->obtener($grupo, $request->user()),
        ]);
    }

    public function update(Request $request, Grupo $grupo): JsonResponse
    {
        $actualizado = $this->grupos->actualizar($grupo, $request->all(), $request->user());
        return response()->json(['data' => $actualizado, 'message' => 'La información se actualizó correctamente']);
    }

    public function destroy(Request $request, Grupo $grupo): JsonResponse
    {
        $this->grupos->eliminar($grupo, $request->user());
        return response()->noContent();
    }

    public function generarCodigo(Request $request, Grupo $grupo): JsonResponse
    {
        $actualizado = $this->grupos->generarCodigoInv($grupo, $request->user());
        return response()->json(['data' => $actualizado, 'message' => 'La información se actualizó correctamente']);
    }
}