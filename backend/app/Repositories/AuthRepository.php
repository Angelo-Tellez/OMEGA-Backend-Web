<?php

/*
 * ============================================================
 * Repositorio — Operaciones de autenticacion en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories;

use App\Models\Usuario;
use App\Repositories\Contracts\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function buscarPorEmail(string $email): ?Usuario
    {
        return Usuario::query()->where('email', $email)->first();
    }

    public function crear(array $datos): Usuario
    {
        return Usuario::query()->create($datos);
    }
}