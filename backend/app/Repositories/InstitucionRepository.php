<?php

/*
 * ============================================================
 * Repositorio — Operaciones de instituciones en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

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