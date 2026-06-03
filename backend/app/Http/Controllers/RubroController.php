<?php

/*
 * ============================================================
 * Controlador API — Gestion de Rubros.
 * MPL-OMEGA-05 §6.1 | §6.2
 * @version 1.0.0
 * ============================================================
 */

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