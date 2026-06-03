<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de rubros.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\RubroEvaluacion;
use Illuminate\Database\Eloquent\Collection;

interface RubroRepositoryInterface
{
    public function porInstitucion(int $institucionId): Collection;
    public function buscarPorId(int $id): ?RubroEvaluacion;
    public function crear(array $datos): RubroEvaluacion;
    public function actualizar(RubroEvaluacion $rubro, array $datos): bool;
    public function eliminar(RubroEvaluacion $rubro): bool;
}