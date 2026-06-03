<?php

/*
 * ============================================================
 * Contrato — Interfaz del repositorio de autenticacion.
 * MPL-OMEGA-05 §2.4
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories\Contracts;

use App\Models\Usuario;

interface AuthRepositoryInterface
{
    public function buscarPorEmail(string $email): ?Usuario;
    public function crear(array $datos): Usuario;
}