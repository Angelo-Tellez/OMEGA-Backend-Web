<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/AsistenciaRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


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