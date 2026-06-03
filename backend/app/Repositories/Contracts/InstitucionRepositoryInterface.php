<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de instituciones.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\Institucion;
use Illuminate\Database\Eloquent\Collection;

interface InstitucionRepositoryInterface
{
    public function todasPorDocente(int $idDocente): Collection;
    public function buscarPorId(int $id): ?Institucion;
    public function crear(array $datos): Institucion;
    public function guardar(Institucion $institucion, array $datos): bool;
    public function eliminar(Institucion $institucion): bool;
}