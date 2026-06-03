<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/GrupoRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


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