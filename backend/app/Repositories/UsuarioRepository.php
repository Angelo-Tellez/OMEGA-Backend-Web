<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/UsuarioRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


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