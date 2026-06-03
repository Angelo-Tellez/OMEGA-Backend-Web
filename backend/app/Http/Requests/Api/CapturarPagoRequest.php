<?php

/*
 * ============================================================
 * Form Request — Validacion de captura de pago PayPal API.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CapturarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'El campo order_id es obligatorio',
        ];
    }
}
