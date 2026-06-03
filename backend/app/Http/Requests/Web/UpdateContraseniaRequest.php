<?php

/*
 * ============================================================
 * Form Request — Validacion de cambio de contrasenia Web.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

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
