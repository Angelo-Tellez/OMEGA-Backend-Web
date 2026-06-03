<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/SesionRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


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