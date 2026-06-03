<?php

namespace App\Http\Controllers;

use App\Models\GrupoAlumno;
use App\Services\GrupoAlumnoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Matriculación de alumnos a grupos.
 * Sin lógica de negocio, solo delega al GrupoAlumnoService.
 */
class GrupoAlumnoController extends Controller
{
    public function __construct(
        private readonly GrupoAlumnoService $grupoAlumnos
    ) {}

    public function index(Request $request, int $idGrupo): JsonResponse
    {
        return response()->json([
            'data' => $this->grupoAlumnos->listarAlumnos($idGrupo, $request->user()),
        ]);
    }

    public function matricular(Request $request): JsonResponse
    {
        $vinculacion = $this->grupoAlumnos->matricular($request->all(), $request->user());
        return response()->json(['data' => $vinculacion, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function destroy(Request $request, GrupoAlumno $grupoAlumno): JsonResponse
    {
        $this->grupoAlumnos->eliminar($grupoAlumno, $request->user());
        return response()->json(['message' => 'Alumno eliminado del grupo']);
    }

    public function destroyPorGrupoAlumno(Request $request, int $idGrupo, int $idAlumno): JsonResponse
    {
        $grupoAlumno = \App\Models\GrupoAlumno::where('id_grupo', $idGrupo)
            ->where('id_alumno', $idAlumno)
            ->firstOrFail();

        $this->grupoAlumnos->eliminar($grupoAlumno, $request->user());
        return response()->json(['message' => 'Alumno eliminado del grupo']);
    }
}