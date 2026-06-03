<?php

/*
 * ============================================================
 * Form Request — Validacion de inicio de sesion Web.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

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
