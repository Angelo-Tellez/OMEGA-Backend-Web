<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Repositories/GrupoRepository.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Repositories;

use App\Models\Grupo;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GrupoRepository implements GrupoRepositoryInterface
{
    public function todosPorDocente(int $idDocente): Collection
    {
        return Grupo::query()
            ->where('id_docente', $idDocente)
            ->orderBy('id_grupo')
            ->get();
    }

    public function todosPorInstitucion(int $idInstitucion, ?int $idDocente = null): Collection
    {
        $query = Grupo::query()
            ->where('id_institucion', $idInstitucion)
            ->orderBy('id_grupo');

        if ($idDocente) {
            $query->where('id_docente', $idDocente);
        }

        return $query->get();
    }

    public function buscarPorId(int $id): ?Grupo
    {
        return Grupo::query()->find($id);
    }

    public function buscarPorCodigoInv(string $codigo): ?Grupo
    {
        return Grupo::query()->where('codigo_inv', $codigo)->first();
    }

    public function crear(array $datos): Grupo
    {
        return Grupo::query()->create($datos);
    }

    public function guardar(Grupo $grupo, array $datos): bool
    {
        return $grupo->update($datos);
    }

    public function eliminar(Grupo $grupo): bool
    {
        return (bool) $grupo->delete();
    }
}