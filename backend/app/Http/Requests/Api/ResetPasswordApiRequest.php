<?php

/*
 * ============================================================
 * Form Request — Validacion de restablecimiento de contrasenia API.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordApiRequest extends FormRequest
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
            'password' => ['required', 'min:6'],
        ];
    }
}
