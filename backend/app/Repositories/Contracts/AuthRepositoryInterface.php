<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/AuthRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories\Contracts;

use App\Models\Usuario;

interface AuthRepositoryInterface
{
    public function buscarPorEmail(string $email): ?Usuario;
    public function crear(array $datos): Usuario;
}