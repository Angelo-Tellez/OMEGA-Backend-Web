<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/AsistenciaRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories\Contracts;

use App\Models\Asistencia;
use Illuminate\Database\Eloquent\Collection;

interface AsistenciaRepositoryInterface
{
    public function todasPorSesion(int $idSesion): Collection;
    public function todasPorAlumno(int $idAlumno): Collection;
    public function buscarPorId(int $id): ?Asistencia;
    public function buscarPorSesionYAlumno(int $idSesion, int $idAlumno): ?Asistencia;
    public function crear(array $datos): Asistencia;
    public function guardar(Asistencia $asistencia, array $datos): bool;
}