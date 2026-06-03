<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Services\InstitucionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Gestión de instituciones del Docente.
 * Sin lógica de negocio, solo delega al InstitucionService.
 */
class InstitucionController extends Controller
{
    public function __construct(
        private readonly InstitucionService $instituciones
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->instituciones->listar($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $creada = $this->instituciones->crear($request->all(), $request->user());
        return response()->json(['data' => $creada, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function show(Request $request, Institucion $institucion): JsonResponse
    {
        return response()->json([
            'data' => $this->instituciones->obtener($institucion, $request->user()),
        ]);
    }

    public function update(Request $request, Institucion $institucion): JsonResponse
    {
        $actualizada = $this->instituciones->actualizar($institucion, $request->all(), $request->user());
        return response()->json(['data' => $actualizada, 'message' => 'La información se actualizó correctamente']);
    }

    public function destroy(Request $request, Institucion $institucion): JsonResponse
    {
        $this->instituciones->eliminar($institucion, $request->user());
        return response()->noContent();
    }
}