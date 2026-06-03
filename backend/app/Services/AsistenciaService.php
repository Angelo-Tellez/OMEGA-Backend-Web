<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/AsistenciaService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Asistencia;
use App\Models\Usuario;
use App\Repositories\Contracts\AsistenciaRepositoryInterface;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\SesionRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    public function __construct(
        private readonly AsistenciaRepositoryInterface $asistencias,
        private readonly SesionRepositoryInterface     $sesiones,
        private readonly GrupoRepositoryInterface      $grupos,
    ) {}

    /**
     * RF-66 — Lista asistencias de una sesión (vista básica sin nombre).
     * Usada por la API sin autenticación de docente para el alumno.
     */
    public function listarPorSesion(int $idSesion): array
    {
        return $this->asistencias->todasPorSesion($idSesion)
            ->map(fn(Asistencia $a) => $this->serializar($a))
            ->values()
            ->all();
    }

    /**
     * RF-66 — Lista asistencias con nombre completo del alumno y hora formateada.
     * Usada por el docente en la vista de sesión activa / historial.
     * Formato nombre: "Ap.Pat Ap.Mat, Nombre"
     * Formato hora: "HH:MM:SS" si está presente, "—" si está ausente
     */
    public function listarPorSesionConAlumnos(int $idSesion, Usuario $docente): array
    {
        $sesion = $this->sesiones->buscarPorId($idSesion);

        if (!$sesion) {
            abort(404, 'Sesión no encontrada.');
        }

        // Verificar que el docente sea dueño del grupo
        $grupo = $this->grupos->buscarPorId($sesion->id_grupo);
        if (!$grupo || $grupo->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException('No tienes permiso para ver esta sesión.');
        }

        return $this->asistencias->todasPorSesion($idSesion)
            ->map(fn(Asistencia $a) => $this->serializarConAlumno($a))
            ->sortBy('nombre_completo')
            ->values()
            ->all();
    }

    /**
     * RF-69 — Calcula el porcentaje de asistencia de un alumno en un grupo.
     * Fórmula: (Presentes + Justificadas) / Total sesiones cerradas * 100
     */
    public function calcularPorcentajeAlumno(int $idGrupo, int $idAlumno): array
    {
        $sesiones = $this->sesiones->todasPorGrupo($idGrupo)
            ->where('est_sesion', 0); // solo cerradas

        $total        = $sesiones->count();
        $presentes    = 0;
        $ausentes     = 0;
        $justificadas = 0;

        foreach ($sesiones as $sesion) {
            $a = $this->asistencias->buscarPorSesionYAlumno($sesion->id_sesion, $idAlumno);
            if ($a) {
                match ($a->est_asistencia) {
                    1 => $presentes++,
                    2 => $ausentes++,
                    3 => $justificadas++,
                    default => null,
                };
            } else {
                $ausentes++;
            }
        }

        $porcentaje = $total > 0
            ? round((($presentes + $justificadas) / $total) * 100, 2)
            : 0.0;

        return [
            'total_sesiones'  => $total,
            'presentes'       => $presentes,
            'ausentes'        => $ausentes,
            'justificadas'    => $justificadas,
            'porcentaje'      => $porcentaje,
        ];
    }

    /**
     * RF-66 — Registrar asistencia desde la web (el docente marca manualmente).
     * Flujo diferente al del alumno: no requiere clave, solo id_sesion + id_alumno.
     */
    public function registrar(array $entrada, Usuario $alumno): array
    {
        $validator = Validator::make($entrada, [
            'clave'    => ['required', 'string'],
            'id_grupo' => ['required', 'integer'],
        ], [
            'clave.required'    => 'La clave de asistencia es obligatoria.',
            'id_grupo.required' => 'El grupo es obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Buscar sesión activa del grupo
        $sesion = $this->sesiones->buscarActivaPorGrupo($entrada['id_grupo']);

        if (!$sesion) {
            throw ValidationException::withMessages([
                'sesion' => ['No hay una sesión activa para este grupo.'],
            ]);
        }

        // Validar clave (insensible a mayúsculas)
        if (strtoupper(trim($entrada['clave'])) !== strtoupper($sesion->clave)) {
            throw ValidationException::withMessages([
                'clave' => ['La clave de asistencia es incorrecta.'],
            ]);
        }

        // Validar duplicado
        $existe = $this->asistencias->buscarPorSesionYAlumno(
            $sesion->id_sesion,
            $alumno->id_usuario
        );

        if ($existe) {
            if ($existe->est_asistencia === 1) {
                throw ValidationException::withMessages([
                    'asistencia' => ['Ya registraste tu asistencia en esta sesión.'],
                ]);
            }

            // Si existe como Ausente, actualizar a Presente
            $this->asistencias->guardar($existe, [
                'est_asistencia' => 1,
                'hora_registro'  => now(),
            ]);

            return $this->serializar($existe->fresh());
        }

        $asistencia = $this->asistencias->crear([
            'id_sesion'      => $sesion->id_sesion,
            'id_alumno'      => $alumno->id_usuario,
            'est_asistencia' => 1,
            'hora_registro'  => now(),
        ]);

        return $this->serializar($asistencia);
    }

    /**
     * RF-67, RF-74 — Editar estado de asistencia por el Docente.
     * est_asistencia: 1=Presente, 2=Ausente, 3=Justificada
     * Al justificar (3), el porcentaje del alumno se recalcula automáticamente
     * porque el AlumnoService siempre lo calcula en tiempo de consulta.
     */
    public function editarEstado(Asistencia $asistencia, array $entrada): array
    {
        $validator = Validator::make($entrada, [
            'est_asistencia' => ['required', 'integer', 'in:1,2,3'],
        ], [
            'est_asistencia.required' => 'El estado de asistencia es obligatorio.',
            'est_asistencia.in'       => 'El estado debe ser 1 (Presente), 2 (Ausente) o 3 (Justificada).',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $datos = ['est_asistencia' => $entrada['est_asistencia']];

        // RF-66 — Registrar hora si se marca como Presente
        if ($entrada['est_asistencia'] === 1 && !$asistencia->hora_registro) {
            $datos['hora_registro'] = now();
        }

        // RF-74 — Si se revierte a Ausente, limpiar la hora
        if ($entrada['est_asistencia'] === 2) {
            $datos['hora_registro'] = null;
        }

        $this->asistencias->guardar($asistencia, $datos);

        return $this->serializarConAlumno($asistencia->fresh());
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers privados
    // ─────────────────────────────────────────────────────────────

    private function serializar(Asistencia $a): array
    {
        return [
            'id_asistencia'  => $a->id_asistencia,
            'id_sesion'      => $a->id_sesion,
            'id_alumno'      => $a->id_alumno,
            'est_asistencia' => $a->est_asistencia,
            'hora_registro'  => $a->hora_registro?->toIso8601String(),
        ];
    }

    /**
     * RF-66 — Serialización con nombre completo y hora formateada.
     */
    private function serializarConAlumno(Asistencia $a): array
    {
        $alumno = $a->alumno;

        return [
            'id_asistencia'   => $a->id_asistencia,
            'id_sesion'       => $a->id_sesion,
            'id_alumno'       => $a->id_alumno,
            // Formato: "Ap.Pat Ap.Mat, Nombre" (RF-66, RF-72)
            'nombre_completo' => $alumno
                ? "{$alumno->ap_pat} {$alumno->ap_mat}, {$alumno->nombre}"
                : null,
            'est_asistencia'  => $a->est_asistencia,
            // RF-66 — Hora HH:MM:SS o "—" para ausentes/sin registro
            'hora_registro'   => $a->hora_registro
                ? $a->hora_registro->format('H:i:s')
                : '—',
        ];
    }
}
