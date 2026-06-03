<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/PagoRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories;

use App\Models\Pago;
use App\Repositories\Contracts\PagoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PagoRepository implements PagoRepositoryInterface
{
    public function todosPorSuscripcion(int $idSuscripcion): Collection
    {
        return Pago::query()
            ->where('id_suscripcion', $idSuscripcion)
            ->orderByDesc('fec_pago')
            ->get();
    }

    public function buscarPorOrderId(string $orderId): ?Pago
    {
        return Pago::query()
            ->where('paypal_order_id', $orderId)
            ->first();
    }

    public function crear(array $datos): Pago
    {
        return Pago::query()->create($datos);
    }

    public function guardar(Pago $pago, array $datos): bool
    {
        return $pago->update($datos);
    }
}