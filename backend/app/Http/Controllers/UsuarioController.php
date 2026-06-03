<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/UsuarioController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — sin lógica de negocio, solo delega al Service.
 */
class UsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioService $usuarios
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->usuarios->listar(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $creado = $this->usuarios->crear($request->all());
        return response()->json(['data' => $creado, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function show(Usuario $usuario): JsonResponse
    {
        return response()->json([
            'data' => $this->usuarios->obtener($usuario),
        ]);
    }

    public function update(Request $request, Usuario $usuario): JsonResponse
    {
        $actualizado = $this->usuarios->actualizar($usuario, $request->all());
        return response()->json(['data' => $actualizado, 'message' => 'La información se actualizó correctamente']);
    }

    public function destroy(Usuario $usuario): JsonResponse
    {
        $this->usuarios->eliminar($usuario);
        return response()->noContent();
    }
}