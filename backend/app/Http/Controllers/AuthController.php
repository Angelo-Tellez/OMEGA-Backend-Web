<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/AuthController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Autenticación de Docentes y Alumnos.
 * Sin lógica de negocio, solo delega al AuthService.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth
    ) {}

    public function registro(Request $request): JsonResponse
    {
        $resultado = $this->auth->registro($request->all());
        return response()->json(['data' => $resultado, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $resultado = $this->auth->login($request->all());
        return response()->json(['data' => $resultado, 'message' => 'La información se cargó correctamente']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->auth->me($request->user()),
        ]);
    }
}