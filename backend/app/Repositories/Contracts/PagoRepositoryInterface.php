<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/Contracts/PagoRepositoryInterface.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories\Contracts;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Collection;

interface PagoRepositoryInterface
{
    public function todosPorSuscripcion(int $idSuscripcion): Collection;
    public function buscarPorOrderId(string $orderId): ?Pago;
    public function crear(array $datos): Pago;
    public function guardar(Pago $pago, array $datos): bool;
}