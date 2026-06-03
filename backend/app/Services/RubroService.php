<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/RubroService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\RubroEvaluacion;
use App\Repositories\Contracts\RubroRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RubroService
{
    public function __construct(
        private readonly RubroRepositoryInterface $rubros
    ) {}

    public function listar(int $institucionId): array
    {
        return $this->rubros->porInstitucion($institucionId)
            ->map(fn(RubroEvaluacion $r) => $this->serializar($r))
            ->values()
            ->all();
    }

    public function obtener(int $id): array
    {
        $rubro = $this->rubros->buscarPorId($id);
        abort_if(!$rubro, 404, 'Rubro no encontrado.');
        return $this->serializar($rubro);
    }

    public function crear(int $institucionId, array $entrada): array
    {
        $datos = $this->validarCreacion($entrada);
        $rubro = $this->rubros->crear([
            'id_institucion'    => $institucionId,
            'nombre'            => $datos['nombre'],
            'porcentaje_minimo' => $datos['porcentaje_minimo'],
        ]);
        return $this->serializar($rubro);
    }

    public function actualizar(int $id, array $entrada): array
    {
        $rubro = $this->rubros->buscarPorId($id);
        abort_if(!$rubro, 404, 'Rubro no encontrado.');
        $datos = $this->validarActualizacion($entrada);
        $this->rubros->actualizar($rubro, $datos);
        return $this->serializar($rubro->fresh());
    }

    public function eliminar(int $id): void
    {
        $rubro = $this->rubros->buscarPorId($id);
        abort_if(!$rubro, 404, 'Rubro no encontrado.');
        $this->rubros->eliminar($rubro);
    }

    private function serializar(RubroEvaluacion $rubro): array
    {
        return [
            'id_rubro'          => $rubro->id_rubro,
            'id_institucion'    => $rubro->id_institucion,
            'nombre'            => $rubro->nombre,
            'porcentaje_minimo' => $rubro->porcentaje_minimo,
        ];
    }

    private function validarCreacion(array $entrada): array
    {
        $validator = Validator::make($entrada, [
            'nombre'            => ['required', 'string', 'max:100'],
            'porcentaje_minimo' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }

    private function validarActualizacion(array $entrada): array
    {
        $validator = Validator::make($entrada, [
            'nombre'            => ['sometimes', 'required', 'string', 'max:100'],
            'porcentaje_minimo' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }
}