<?php

/*
 * ============================================================
 * Repositorio — Operaciones de sesiones en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories;

use App\Models\Sesion;
use App\Repositories\Contracts\SesionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SesionRepository implements SesionRepositoryInterface
{
    public function todasPorGrupo(int $idGrupo): Collection
    {
        return Sesion::query()
            ->where('id_grupo', $idGrupo)
            ->orderByDesc('id_sesion')
            ->get();
    }

    public function buscarPorId(int $id): ?Sesion
    {
        return Sesion::query()->find($id);
    }

    public function buscarActivaPorGrupo(int $idGrupo): ?Sesion
    {
        return Sesion::query()
            ->where('id_grupo', $idGrupo)
            ->where('est_sesion', 1)
            ->first();
    }

    public function crear(array $datos): Sesion
    {
        return Sesion::query()->create($datos);
    }

    public function guardar(Sesion $sesion, array $datos): bool
    {
        return $sesion->update($datos);
    }
}