<?php

namespace App\Http\Controllers;

use App\Services\SuscripcionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Gestión de suscripciones del Docente.
 * Sin lógica de negocio, solo delega al SuscripcionService.
 */
class SuscripcionController extends Controller
{
    public function __construct(
        private readonly SuscripcionService $suscripciones
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->suscripciones->obtener($request->user()),
        ]);
    }

    public function activarBasico(Request $request): JsonResponse
    {
        $suscripcion = $this->suscripciones->crearPlanBasico($request->user());
        return response()->json(['data' => $this->suscripciones->obtener($request->user()), 'message' => 'La información se cargó correctamente']);
    }
}