<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : backend/app/Http/Requests/Web/RegistroRequest.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                   => ['required', 'string', 'max:100'],
            'ap_pat'                   => ['required', 'string', 'max:100'],
            'ap_mat'                   => ['required', 'string', 'max:100'],
            'email'                    => ['required', 'email', 'max:200', 'unique:usuarios,email'],
            'contrasenia'              => ['required', 'string', 'min:8', 'confirmed'],
            'contrasenia_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El campo nombre es obligatorio',
            'ap_pat.required'    => 'El campo apellido paterno es obligatorio',
            'ap_mat.required'    => 'El campo apellido materno es obligatorio',
            'email.required'     => 'El campo correo electrónico es obligatorio',
            'email.email'        => 'El correo no tiene un formato válido',
            'email.unique'       => 'El correo electrónico ya está registrado',
            'contrasenia.required' => 'El campo contraseña es obligatorio',
            'contrasenia.min'      => 'La contraseña debe tener al menos 8 caracteres',
            'contrasenia.confirmed'=> 'Los campos contraseña y confirmar contraseña deben coincidir',
        ];
    }
}
