<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/AuthService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service — Lógica de negocio de autenticación.
 * Registro, login y logout de Docentes y Alumnos.
 */
class AuthService
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios
    ) {}

    public function registro(array $entrada): array
    {
        // Aceptar 'password' como alias de 'contrasenia' (app móvil)
        if (!isset($entrada['contrasenia']) && isset($entrada['password'])) {
            $entrada['contrasenia']              = $entrada['password'];
            $entrada['contrasenia_confirmation'] = $entrada['password_confirmation'] ?? $entrada['password'];
        }

        $validator = Validator::make($entrada, [
            'nombre'      => ['required', 'string', 'max:100'],
            'ap_pat'      => ['required', 'string', 'max:100'],
            'ap_mat'      => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:200', 'unique:usuarios,email'],
            'contrasenia' => ['required', 'string', 'min:6'],
            'rol'         => ['required', 'integer', 'in:1,2'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $datos = $validator->validated();
        $usuario = $this->usuarios->crear($datos);
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => $this->serializar($usuario),
            'token'   => $token,
        ];
    }

    public function login(array $entrada): array
    {
        // Aceptar 'password' como alias de 'contrasenia' (app móvil)
        if (!isset($entrada['contrasenia']) && isset($entrada['password'])) {
            $entrada['contrasenia'] = $entrada['password'];
        }

        $validator = Validator::make($entrada, [
            'email'       => ['required', 'email'],
            'contrasenia' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $usuario = $this->usuarios->buscarPorEmail($entrada['email']);

        if (!$usuario || !Hash::check($entrada['contrasenia'], $usuario->contrasenia)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => $this->serializar($usuario),
            'token'   => $token,
        ];
    }

    public function logout(Usuario $usuario): void
    {
        $usuario->currentAccessToken()->delete();
    }

    public function me(Usuario $usuario): array
    {
        return $this->serializar($usuario);
    }

    private function serializar(Usuario $usuario): array
    {
        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->nombre,
            'ap_pat'     => $usuario->ap_pat,
            'ap_mat'     => $usuario->ap_mat,
            'email'      => $usuario->email,
            'rol'        => $usuario->rol,
        ];
    }
}