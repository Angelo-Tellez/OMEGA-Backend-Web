<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de grupos.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\Grupo;
use Illuminate\Database\Eloquent\Collection;

interface GrupoRepositoryInterface
{
    public function todosPorDocente(int $idDocente): Collection;
    public function todosPorInstitucion(int $idInstitucion): Collection;
    public function buscarPorId(int $id): ?Grupo;
    public function buscarPorCodigoInv(string $codigo): ?Grupo;
    public function crear(array $datos): Grupo;
    public function guardar(Grupo $grupo, array $datos): bool;
    public function eliminar(Grupo $grupo): bool;
}