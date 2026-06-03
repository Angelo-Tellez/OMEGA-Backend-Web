<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/SuscripcionRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories\Contracts;

use App\Models\Suscripcion;

interface SuscripcionRepositoryInterface
{
    public function buscarPorUsuario(int $idUsuario): ?Suscripcion;
    public function buscarPorId(int $id): ?Suscripcion;
    public function crear(array $datos): Suscripcion;
    public function guardar(Suscripcion $suscripcion, array $datos): bool;
}