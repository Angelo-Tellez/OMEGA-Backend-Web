<?php

/*
 * ============================================================
 * Form Request — Validacion de restablecimiento de contrasenia Web.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'El campo correo electrónico es obligatorio',
            'email.email'       => 'El correo no tiene un formato válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed'=> 'Los campos contraseña y confirmar contraseña deben coincidir',
        ];
    }
}
