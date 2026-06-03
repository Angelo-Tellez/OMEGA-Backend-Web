<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de asistencias.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\Asistencia;
use Illuminate\Database\Eloquent\Collection;

interface AsistenciaRepositoryInterface
{
    public function todasPorSesion(int $idSesion): Collection;
    public function todasPorAlumno(int $idAlumno): Collection;
    public function buscarPorId(int $id): ?Asistencia;
    public function buscarPorSesionYAlumno(int $idSesion, int $idAlumno): ?Asistencia;
    public function crear(array $datos): Asistencia;
    public function guardar(Asistencia $asistencia, array $datos): bool;
}