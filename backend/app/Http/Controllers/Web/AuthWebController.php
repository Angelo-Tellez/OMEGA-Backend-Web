<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : backend/app/Http/Controllers/Web/AuthWebController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Http\Requests\Web\LoginRequest;
use App\Http\Requests\Web\RegistroRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controlador Web — Autenticación de Docentes.
 * Maneja login, registro y logout para las vistas Blade.
 * @version 1.0.0
 */
class AuthWebController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('ca.dashboard.index');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $attempt = Auth::attempt([
            'email'      => $credentials['email'],
            'password'   => $credentials['contrasenia'],
        ]);

        if (!$attempt) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos',
            ]);
        }

        // Solo docentes pueden acceder al panel web
        if (Auth::user()->rol !== 1) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'No tienes permisos para acceder al panel web',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('ca.dashboard.index');
    }

    public function showRegistro()
    {
        if (Auth::check()) {
            return redirect()->route('ca.dashboard.index');
        }
        return view('auth.registro');
    }

    public function registro(RegistroRequest $request)
    {
        $request->validated();

        try {
            $this->authService->registro([
                'nombre'                  => $request->nombre,
                'ap_pat'                  => $request->ap_pat,
                'ap_mat'                  => $request->ap_mat,
                'email'                   => $request->email,
                'contrasenia'             => $request->contrasenia,
                'contrasenia_confirmation'=> $request->contrasenia_confirmation,
                'rol'                     => 1, // Docente
            ]);

            Auth::attempt([
                'email'    => $request->email,
                'password' => $request->contrasenia,
            ]);

            $request->session()->regenerate();

            return redirect()->route('ca.dashboard.index')
                ->with('success', 'Cuenta creada correctamente');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('ca.login');
    }
}