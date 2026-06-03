<?php

/*
 * ============================================================
 * Form Request — Validacion de creacion de periodo Web.
 * MPL-OMEGA-05 §6.5
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $anioActual    = now()->year;
        $anioSiguiente = $anioActual + 1;

        if ($this->filled('nombre_rapido')) {
            return [
                'nombre_rapido' => ['required', 'string', 'max:100'],
            ];
        }

        return [
            'fecha_inicio' => [
                'required', 'date',
                "after_or_equal:{$anioActual}-01-01",
                "before_or_equal:{$anioSiguiente}-12-31",
            ],
            'fecha_fin' => [
                'required', 'date',
                'after_or_equal:fecha_inicio',
                "before_or_equal:{$anioSiguiente}-12-31",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_rapido.required' => 'El campo nombre del periodo es obligatorio',
            'fecha_inicio.required'  => 'El campo fecha de inicio es obligatorio',
            'fecha_inicio.date'      => 'El campo fecha de inicio no tiene un formato válido',
            'fecha_fin.required'     => 'El campo fecha de fin es obligatorio',
            'fecha_fin.date'         => 'El campo fecha de fin no tiene un formato válido',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ];
    }
}
