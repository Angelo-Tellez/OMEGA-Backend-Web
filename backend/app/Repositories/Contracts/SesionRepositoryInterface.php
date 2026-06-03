<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de sesiones.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\Sesion;
use Illuminate\Database\Eloquent\Collection;

interface SesionRepositoryInterface
{
    public function todasPorGrupo(int $idGrupo): Collection;
    public function buscarPorId(int $id): ?Sesion;
    public function buscarActivaPorGrupo(int $idGrupo): ?Sesion;
    public function crear(array $datos): Sesion;
    public function guardar(Sesion $sesion, array $datos): bool;
}