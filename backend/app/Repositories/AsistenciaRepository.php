<?php

/*
 * ============================================================
 * Repositorio — Operaciones de asistencias en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories;

use App\Models\Asistencia;
use App\Repositories\Contracts\AsistenciaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AsistenciaRepository implements AsistenciaRepositoryInterface
{
    public function todasPorSesion(int $idSesion): Collection
    {
        return Asistencia::query()
            ->where('id_sesion', $idSesion)
            ->orderBy('hora_registro')
            ->get();
    }

    public function todasPorAlumno(int $idAlumno): Collection
    {
        return Asistencia::query()
            ->where('id_alumno', $idAlumno)
            ->orderByDesc('id_asistencia')
            ->get();
    }

    public function buscarPorId(int $id): ?Asistencia
    {
        return Asistencia::query()->find($id);
    }

    public function buscarPorSesionYAlumno(int $idSesion, int $idAlumno): ?Asistencia
    {
        return Asistencia::query()
            ->where('id_sesion', $idSesion)
            ->where('id_alumno', $idAlumno)
            ->first();
    }

    public function crear(array $datos): Asistencia
    {
        return Asistencia::query()->create($datos);
    }

    public function guardar(Asistencia $asistencia, array $datos): bool
    {
        return $asistencia->update($datos);
    }
}