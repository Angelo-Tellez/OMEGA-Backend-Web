<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : backend/app/Http/Requests/Web/LoginRequest.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'       => ['required', 'email'],
            'contrasenia' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'       => 'El campo correo electrónico es obligatorio',
            'email.email'          => 'El correo no tiene un formato válido',
            'contrasenia.required' => 'El campo contraseña es obligatorio',
        ];
    }
}
