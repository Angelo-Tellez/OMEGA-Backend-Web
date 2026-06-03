<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de rubros de evaluacion.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\RubroEvaluacion;
use Illuminate\Database\Eloquent\Collection;

interface RubroEvaluacionRepositoryInterface
{
    public function todosPorInstitucion(int $idInstitucion): Collection;
    public function buscarPorId(int $id): ?RubroEvaluacion;
    public function crear(array $datos): RubroEvaluacion;
    public function guardar(RubroEvaluacion $rubro, array $datos): bool;
    public function eliminar(RubroEvaluacion $rubro): bool;
}