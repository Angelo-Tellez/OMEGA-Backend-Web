<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/GrupoAlumnoRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories\Contracts;

use App\Models\GrupoAlumno;
use Illuminate\Database\Eloquent\Collection;

interface GrupoAlumnoRepositoryInterface
{
    public function alumnosPorGrupo(int $idGrupo): Collection;
    public function gruposPorAlumno(int $idAlumno): Collection;
    public function buscarVinculacion(int $idGrupo, int $idAlumno): ?GrupoAlumno;
    public function crear(array $datos): GrupoAlumno;
    public function eliminar(GrupoAlumno $grupoAlumno): bool;
}