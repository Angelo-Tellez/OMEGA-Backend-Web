<?php

/*
 * ============================================================
 * AsistenciaController
 * MPL-OMEGA-05 | Código: CA-CTRL-ASISTENCIA-01
 * ============================================================
 * Controlador HTTP para registro y gestión de asistencias.
 * Sin lógica de negocio: delega al AsistenciaService.
 *
 * Requerimientos: RF-66, RF-67, RF-69, RF-74
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Services\AsistenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(
        private readonly AsistenciaService $asistencias
    ) {}

    /**
     * RF-66 — Lista asistencias de una sesión (vista básica).
     * GET /api/sesiones/{idSesion}/asistencias
     */
    public function porSesion(int $idSesion): JsonResponse
    {
        return response()->json([
            'data' => $this->asistencias->listarPorSesion($idSesion),
        ]);
    }

    /**
     * RF-66 — Lista asistencias con nombre completo y hora formateada (vista docente).
     * GET /api/sesiones/{idSesion}/asistencias/detalle
     */
    public function porSesionConAlumnos(Request $request, int $idSesion): JsonResponse
    {
        return response()->json([
            'data' => $this->asistencias->listarPorSesionConAlumnos($idSesion, $request->user()),
        ]);
    }

    /**
     * RF-69 — Porcentaje de asistencia de un alumno en un grupo.
     * GET /api/grupos/{idGrupo}/alumnos/{idAlumno}/porcentaje
     */
    public function porcentajeAlumno(int $idGrupo, int $idAlumno): JsonResponse
    {
        return response()->json([
            'data' => $this->asistencias->calcularPorcentajeAlumno($idGrupo, $idAlumno),
        ]);
    }

    /**
     * RF-66 — Registro de asistencia por clave (desde el alumno vía web/API).
     * POST /api/asistencias/registrar
     */
    public function registrar(Request $request): JsonResponse
    {
        $asistencia = $this->asistencias->registrar($request->all(), $request->user());
        return response()->json(['data' => $asistencia, 'message' => 'El registro se realizó correctamente'], 201);
    }

    /**
     * RF-67, RF-74 — Editar estado de asistencia (docente).
     * PUT /api/asistencias/{asistencia}/estado
     */
    public function editarEstado(Request $request, Asistencia $asistencia): JsonResponse
    {
        $actualizada = $this->asistencias->editarEstado($asistencia, $request->all());
        return response()->json(['data' => $actualizada, 'message' => 'La información se actualizó correctamente']);
    }

    /**
     * PATCH /sesiones/{idSesion}/alumnos/{idAlumno}/asistencia
     * Edita la asistencia de un alumno en una sesión específica.
     */
    public function editarPorSesionAlumno(Request $request, int $idSesion, int $idAlumno): JsonResponse
    {
        $asistencia = \App\Models\Asistencia::where('id_sesion', $idSesion)
            ->where('id_alumno', $idAlumno)
            ->first();

        if (!$asistencia) {
            // Crear la asistencia si no existe
            $asistencia = \App\Models\Asistencia::create([
                'id_sesion'      => $idSesion,
                'id_alumno'      => $idAlumno,
                'est_asistencia' => $request->input('est_asistencia', 2),
                'hora_registro'  => now(),
            ]);
        } else {
            $actualizada = $this->asistencias->editarEstado($asistencia, $request->all());
        }

        return response()->json(['data' => $asistencia->fresh(), 'message' => 'La información se actualizó correctamente']);
    }
}
