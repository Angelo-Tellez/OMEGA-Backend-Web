<?php

/*
 * ============================================================
 * Repositorio — Operaciones de inscripciones alumno-grupo en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories;

use App\Models\GrupoAlumno;
use App\Repositories\Contracts\GrupoAlumnoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GrupoAlumnoRepository implements GrupoAlumnoRepositoryInterface
{
    public function alumnosPorGrupo(int $idGrupo): Collection
    {
        return GrupoAlumno::query()
            ->where('id_grupo', $idGrupo)
            ->orderBy('fec_inscripcion')
            ->get();
    }

    public function gruposPorAlumno(int $idAlumno): Collection
    {
        return GrupoAlumno::query()
            ->where('id_alumno', $idAlumno)
            ->orderByDesc('fec_inscripcion')
            ->get();
    }

    public function buscarVinculacion(int $idGrupo, int $idAlumno): ?GrupoAlumno
    {
        return GrupoAlumno::query()
            ->where('id_grupo', $idGrupo)
            ->where('id_alumno', $idAlumno)
            ->first();
    }

    public function crear(array $datos): GrupoAlumno
    {
        return GrupoAlumno::query()->create($datos);
    }

    public function eliminar(GrupoAlumno $grupoAlumno): bool
    {
        return (bool) $grupoAlumno->delete();
    }
}