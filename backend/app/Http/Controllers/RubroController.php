<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/RubroController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Services\RubroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RubroController extends Controller
{
    public function __construct(
        private readonly RubroService $rubros
    ) {}

    public function index(int $institucion): JsonResponse
    {
        $data = $this->rubros->listar($institucion);
        return response()->json(['data' => $data, 'message' => 'La información se cargó correctamente']);
    }

    public function store(Request $request, int $institucion): JsonResponse
    {
        $data = $this->rubros->crear($institucion, $request->all());
        return response()->json(['data' => $data, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function show(int $rubro): JsonResponse
    {
        $data = $this->rubros->obtener($rubro);
        return response()->json(['data' => $data, 'message' => 'La información se cargó correctamente']);
    }

    public function update(Request $request, int $rubro): JsonResponse
    {
        $data = $this->rubros->actualizar($rubro, $request->all());
        return response()->json(['data' => $data, 'message' => 'La información se cargó correctamente']);
    }

    public function destroy(int $rubro): Response
    {
        $this->rubros->eliminar($rubro);
        return response()->noContent();
    }
}