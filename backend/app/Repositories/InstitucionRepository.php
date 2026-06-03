<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/InstitucionRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories;

use App\Models\Institucion;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InstitucionRepository implements InstitucionRepositoryInterface
{
    public function todasPorDocente(int $idDocente): Collection
    {
        return Institucion::query()
            ->where('id_docente', $idDocente)
            ->orderBy('id_institucion')
            ->get();
    }

    public function buscarPorId(int $id): ?Institucion
    {
        return Institucion::query()->find($id);
    }

    public function crear(array $datos): Institucion
    {
        return Institucion::query()->create($datos);
    }

    public function guardar(Institucion $institucion, array $datos): bool
    {
        return $institucion->update($datos);
    }

    public function eliminar(Institucion $institucion): bool
    {
        return (bool) $institucion->delete();
    }
}