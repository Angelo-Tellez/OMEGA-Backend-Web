<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de pagos.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

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