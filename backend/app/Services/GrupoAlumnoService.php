<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/GrupoAlumnoService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\GrupoAlumno;
use App\Models\Usuario;
use App\Repositories\Contracts\GrupoAlumnoRepositoryInterface;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GrupoAlumnoService
{
    public function __construct(
        private readonly GrupoAlumnoRepositoryInterface $grupoAlumnos,
        private readonly GrupoRepositoryInterface       $grupos,
    ) {}

    public function listarAlumnos(int $idGrupo, Usuario $docente): array
    {
        $grupo = $this->grupos->buscarPorId($idGrupo);

        if (!$grupo || $grupo->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException('No tienes permiso para acceder a este grupo.');
        }

        return $this->grupoAlumnos->alumnosPorGrupo($idGrupo)
            ->map(fn(GrupoAlumno $ga) => $this->serializar($ga))
            ->values()
            ->all();
    }

    public function matricular(array $entrada, Usuario $alumno): array
    {
        $validator = Validator::make($entrada, [
            'codigo_inv' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Buscar grupo por código
        $grupo = $this->grupos->buscarPorCodigoInv($entrada['codigo_inv']);

        if (!$grupo) {
            throw ValidationException::withMessages([
                'codigo_inv' => ['El código de invitación no existe o no es válido.'],
            ]);
        }

        // Verificar que no esté ya matriculado
        $vinculacion = $this->grupoAlumnos->buscarVinculacion($grupo->id_grupo, $alumno->id_usuario);
        if ($vinculacion) {
            throw ValidationException::withMessages([
                'codigo_inv' => ['Ya estás matriculado en este grupo.'],
            ]);
        }

        // Verificar capacidad máxima
        $totalAlumnos = $this->grupoAlumnos->alumnosPorGrupo($grupo->id_grupo)->count();
        if ($totalAlumnos >= $grupo->no_alumnos) {
            throw ValidationException::withMessages([
                'codigo_inv' => ['El grupo ha alcanzado su capacidad máxima.'],
            ]);
        }

        $grupoAlumno = $this->grupoAlumnos->crear([
            'id_grupo'        => $grupo->id_grupo,
            'id_alumno'       => $alumno->id_usuario,
            'fec_inscripcion' => now()->toDateString(),
        ]);

        return $this->serializar($grupoAlumno);
    }

    public function eliminar(GrupoAlumno $grupoAlumno, Usuario $docente): void
    {
        $grupo = $this->grupos->buscarPorId($grupoAlumno->id_grupo);

        if (!$grupo || $grupo->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException('No tienes permiso para realizar esta acción.');
        }

        $this->grupoAlumnos->eliminar($grupoAlumno);
    }

    private function serializar(GrupoAlumno $grupoAlumno): array
    {
        $alumno        = $grupoAlumno->alumno;
        $idGrupo       = $grupoAlumno->id_grupo;
        $idAlumno      = $grupoAlumno->id_alumno;

        // Calcular estadísticas de asistencia
        $sesiones      = \App\Models\Sesion::where('id_grupo', $idGrupo)->where('est_sesion', 0)->count();
        $asistidas     = \App\Models\Asistencia::whereHas('sesion', fn($q) => $q->where('id_grupo', $idGrupo))
            ->where('id_alumno', $idAlumno)
            ->where('est_asistencia', 1)
            ->count();

        return [
            'id_grupo_alumno'   => $grupoAlumno->id_grupo_alumno,
            'id_grupo'          => $idGrupo,
            'alumno_id'         => $idAlumno,
            'nombre'            => $alumno?->nombre            ?? '',
            'ap_pat'            => $alumno?->ap_pat            ?? '',
            'ap_mat'            => $alumno?->ap_mat            ?? '',
            'email'             => $alumno?->email             ?? '',
            'total_sesiones'    => $sesiones,
            'sesiones_asistidas'=> $asistidas,
            'fecha_inscripcion' => $grupoAlumno->fec_inscripcion?->toDateString() ?? '',
        ];
    }
}