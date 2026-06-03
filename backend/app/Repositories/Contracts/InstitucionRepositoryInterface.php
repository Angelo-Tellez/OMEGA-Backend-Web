<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/InstitucionRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


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