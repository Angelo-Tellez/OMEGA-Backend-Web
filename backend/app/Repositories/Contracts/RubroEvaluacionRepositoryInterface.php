<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/RubroEvaluacionRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


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