<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/RubroRepositoryInterface.php
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

interface RubroRepositoryInterface
{
    public function porInstitucion(int $institucionId): Collection;
    public function buscarPorId(int $id): ?RubroEvaluacion;
    public function crear(array $datos): RubroEvaluacion;
    public function actualizar(RubroEvaluacion $rubro, array $datos): bool;
    public function eliminar(RubroEvaluacion $rubro): bool;
}