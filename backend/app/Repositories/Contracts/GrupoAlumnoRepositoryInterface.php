<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de inscripciones.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\GrupoAlumno;
use Illuminate\Database\Eloquent\Collection;

interface GrupoAlumnoRepositoryInterface
{
    public function alumnosPorGrupo(int $idGrupo): Collection;
    public function gruposPorAlumno(int $idAlumno): Collection;
    public function buscarVinculacion(int $idGrupo, int $idAlumno): ?GrupoAlumno;
    public function crear(array $datos): GrupoAlumno;
    public function eliminar(GrupoAlumno $grupoAlumno): bool;
}