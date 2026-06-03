<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de suscripciones.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\Suscripcion;

interface SuscripcionRepositoryInterface
{
    public function buscarPorUsuario(int $idUsuario): ?Suscripcion;
    public function buscarPorId(int $id): ?Suscripcion;
    public function crear(array $datos): Suscripcion;
    public function guardar(Suscripcion $suscripcion, array $datos): bool;
}