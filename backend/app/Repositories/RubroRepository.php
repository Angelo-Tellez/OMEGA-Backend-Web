<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/RubroRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories;

use App\Models\RubroEvaluacion;
use App\Repositories\Contracts\RubroRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RubroRepository implements RubroRepositoryInterface
{
    public function porInstitucion(int $institucionId): Collection
    {
        return RubroEvaluacion::query()
            ->where('id_institucion', $institucionId)
            ->orderBy('id_rubro')
            ->get();
    }

    public function buscarPorId(int $id): ?RubroEvaluacion
    {
        return RubroEvaluacion::query()->find($id);
    }

    public function crear(array $datos): RubroEvaluacion
    {
        return RubroEvaluacion::query()->create($datos);
    }

    public function actualizar(RubroEvaluacion $rubro, array $datos): bool
    {
        return $rubro->update($datos);
    }

    public function eliminar(RubroEvaluacion $rubro): bool
    {
        return (bool) $rubro->delete();
    }
}