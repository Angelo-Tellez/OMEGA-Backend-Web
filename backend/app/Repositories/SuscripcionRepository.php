<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/SuscripcionRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories;

use App\Models\Suscripcion;
use App\Repositories\Contracts\SuscripcionRepositoryInterface;

class SuscripcionRepository implements SuscripcionRepositoryInterface
{
    public function buscarPorUsuario(int $idUsuario): ?Suscripcion
    {
        return Suscripcion::query()
            ->where('id_usuario', $idUsuario)
            ->first();
    }

    public function buscarPorId(int $id): ?Suscripcion
    {
        return Suscripcion::query()->find($id);
    }

    public function crear(array $datos): Suscripcion
    {
        return Suscripcion::query()->create($datos);
    }

    public function guardar(Suscripcion $suscripcion, array $datos): bool
    {
        return $suscripcion->update($datos);
    }
}