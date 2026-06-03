<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/RubroEvaluacionService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Institucion;
use App\Models\RubroEvaluacion;
use App\Models\Usuario;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Repositories\Contracts\RubroEvaluacionRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RubroEvaluacionService
{
    public function __construct(
        private readonly RubroEvaluacionRepositoryInterface $rubros,
        private readonly InstitucionRepositoryInterface     $instituciones,
    ) {}

    public function listar(int $idInstitucion, Usuario $docente): array
    {
        $institucion = $this->instituciones->buscarPorId($idInstitucion);
        $this->verificarPropietario($institucion, $docente);

        return $this->rubros->todosPorInstitucion($idInstitucion)
            ->map(fn(RubroEvaluacion $r) => $this->serializar($r))
            ->values()
            ->all();
    }

    public function crear(int $idInstitucion, array $entrada, Usuario $docente): array
    {
        $institucion = $this->instituciones->buscarPorId($idInstitucion);
        $this->verificarPropietario($institucion, $docente);

        $datos = $this->validar($entrada);
        $datos['id_institucion'] = $idInstitucion;
        $rubro = $this->rubros->crear($datos);

        return $this->serializar($rubro);
    }

    public function actualizar(RubroEvaluacion $rubro, array $entrada, Usuario $docente): array
    {
        $institucion = $this->instituciones->buscarPorId($rubro->id_institucion);
        $this->verificarPropietario($institucion, $docente);

        $datos = $this->validar($entrada);
        $this->rubros->guardar($rubro, $datos);

        return $this->serializar($rubro->fresh());
    }

    public function eliminar(RubroEvaluacion $rubro, Usuario $docente): void
    {
        $institucion = $this->instituciones->buscarPorId($rubro->id_institucion);
        $this->verificarPropietario($institucion, $docente);
        $this->rubros->eliminar($rubro);
    }

    private function verificarPropietario(?Institucion $institucion, Usuario $docente): void
    {
        if (!$institucion || $institucion->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException('No tienes permiso para acceder a esta institución.');
        }
    }

    private function validar(array $entrada): array
    {
        $validator = Validator::make($entrada, [
            'nombre'             => ['required', 'string', 'max:100'],
            'porcentaje_minimo'  => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function serializar(RubroEvaluacion $rubro): array
    {
        return [
            'id_rubro'            => $rubro->id_rubro,
            'id_institucion'      => $rubro->id_institucion,
            'nombre'              => $rubro->nombre,
            'porcentaje_minimo'   => $rubro->porcentaje_minimo,
            'created_at'          => $rubro->created_at?->toIso8601String(),
        ];
    }
}