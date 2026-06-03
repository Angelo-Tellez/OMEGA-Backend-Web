<?php

/*
 * ============================================================
 * Form Request — Validacion de registro de nuevo Docente Web.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

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
