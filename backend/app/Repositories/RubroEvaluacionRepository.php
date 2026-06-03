<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/RubroEvaluacionRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories;

use App\Models\RubroEvaluacion;
use App\Repositories\Contracts\RubroEvaluacionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RubroEvaluacionRepository implements RubroEvaluacionRepositoryInterface
{
    public function todosPorInstitucion(int $idInstitucion): Collection
    {
        return RubroEvaluacion::query()
            ->where('id_institucion', $idInstitucion)
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

    public function guardar(RubroEvaluacion $rubro, array $datos): bool
    {
        return $rubro->update($datos);
    }

    public function eliminar(RubroEvaluacion $rubro): bool
    {
        return (bool) $rubro->delete();
    }
}