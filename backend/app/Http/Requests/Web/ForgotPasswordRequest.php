<?php

/*
 * ============================================================
 * Form Request — Validacion de solicitud de recuperacion de contrasenia Web.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El campo correo electrónico es obligatorio',
            'email.email'    => 'El correo no tiene un formato válido',
        ];
    }
}
