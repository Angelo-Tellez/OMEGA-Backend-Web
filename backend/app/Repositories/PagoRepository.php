<?php

/*
 * ============================================================
 * Repositorio — Operaciones de pagos en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

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