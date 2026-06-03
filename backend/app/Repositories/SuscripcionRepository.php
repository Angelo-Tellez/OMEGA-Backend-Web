<?php

/*
 * ============================================================
 * Repositorio — Operaciones de suscripciones en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

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