<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : backend/app/Http/Requests/Web/UpdateContraseniaRequest.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContraseniaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contrasenia_actual'             => ['required', 'string'],
            'contrasenia_nueva'              => ['required', 'string', 'min:8', 'confirmed'],
            'contrasenia_nueva_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'contrasenia_actual.required'   => 'El campo contraseña actual es obligatorio',
            'contrasenia_nueva.required'    => 'El campo contraseña nueva es obligatorio',
            'contrasenia_nueva.min'         => 'La contraseña nueva debe tener al menos 8 caracteres',
            'contrasenia_nueva.confirmed'   => 'Los campos contraseña nueva y confirmar contraseña deben coincidir',
        ];
    }
}
