<?php

/*
 * ============================================================
 * Repositorio — Operaciones de usuarios en BD.
 * MPL-OMEGA-05 §2.4 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Repositories;

use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function todos(): Collection
    {
        return Usuario::query()->orderBy('id_usuario')->get();
    }

    public function buscarPorId(int $id): ?Usuario
    {
        return Usuario::query()->find($id);
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        return Usuario::query()->where('email', $email)->first();
    }

    public function crear(array $datos): Usuario
    {
        return Usuario::query()->create($datos);
    }

    public function guardar(Usuario $usuario, array $datos): bool
    {
        return $usuario->update($datos);
    }

    public function eliminar(Usuario $usuario): bool
    {
        return (bool) $usuario->delete();
    }
}