<?php

/*
 * ============================================================
 * AlumnoController
 * MPL-OMEGA-05 | Código: CA-CTRL-ALUMNO-01
 * ============================================================
 * Controlador HTTP para las operaciones del rol Alumno
 * consumidas desde la aplicación móvil Flutter.
 *
 * Sin lógica de negocio: delega 100 % al AlumnoService.
 * Requerimientos: RF-14, RF-15, RF-19, RF-21, RF-31..RF-45
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Services\AlumnoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function __construct(
        private readonly AlumnoService $alumnos
    ) {}

    /**
     * RF-19 — Matriculación por código de invitación.
     * POST /api/alumno/grupos/unirse
     * Body: { "codigo_inv": "XXXXXXXX" }
     */
    public function unirse(Request $request): JsonResponse
    {
        $vinculacion = $this->alumnos->unirse($request->all(), $request->user());
        return response()->json(['data' => $vinculacion, 'message' => 'El registro se realizó correctamente'], 201);
    }

    /**
     * RF-15, RF-32, RF-33, RF-41, RF-43 — Panel de progreso del alumno.
     * GET /api/alumno/grupos
     * Retorna lista de grupos con % asistencia, rubros y contadores.
     */
    public function misGrupos(Request $request): JsonResponse
    {
        $grupos = $this->alumnos->misGrupos($request->user());
        return response()->json(['data' => $grupos, 'message' => 'La información se cargó correctamente']);
    }

    /**
     * RF-14, RF-21, RF-38 — Registro de asistencia con clave temporal.
     * POST /api/alumno/asistencia
     * Body: { "id_grupo": 1, "clave": "ABC123" }
     */
    public function registrarAsistencia(Request $request): JsonResponse
    {
        $asistencia = $this->alumnos->registrarAsistencia($request->all(), $request->user());
        return response()->json(['data' => $asistencia, 'message' => 'El registro se realizó correctamente'], 201);
    }

    /**
     * RF-31, RF-32, RF-33 — Historial de asistencia por materia con estados.
     * GET /api/alumno/grupos/{idGrupo}/historial
     * Retorna sesiones con est_asistencia: 1=Presente, 2=Ausente, 3=Justificada
     */
    public function historialGrupo(Request $request, int $idGrupo): JsonResponse
    {
        $historial = $this->alumnos->historialGrupo($idGrupo, $request->user());
        return response()->json(['data' => $historial, 'message' => 'La información se cargó correctamente']);
    }
}
