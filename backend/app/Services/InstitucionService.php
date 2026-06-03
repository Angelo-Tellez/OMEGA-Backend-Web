<?php

/*
 * ============================================================
 * Servicio — Logica de negocio de instituciones.
 * MPL-OMEGA-05 §2.3 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Services;

use App\Models\Institucion;
use App\Models\Usuario;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class InstitucionService
{
    public function __construct(
        private readonly InstitucionRepositoryInterface $instituciones
    ) {}

    public function listar(Usuario $docente): array
    {
        return $this->instituciones->todasPorDocente($docente->id_usuario)
            ->map(fn(Institucion $i) => $this->serializar($i))
            ->values()
            ->all();
    }

    public function obtener(Institucion $institucion, Usuario $docente): array
    {
        $this->verificarPropietario($institucion, $docente);
        return $this->serializar($institucion);
    }

    public function crear(array $entrada, Usuario $docente): array
    {
        $datos = $this->validar($entrada);
        $datos['id_docente'] = $docente->id_usuario;
        $institucion = $this->instituciones->crear($datos);
        return $this->serializar($institucion);
    }

    public function crearModelo(array $entrada, Usuario $docente): \App\Models\Institucion
    {
        $datos = $this->validar($entrada);
        $datos['id_docente'] = $docente->id_usuario;
        return $this->instituciones->crear($datos);
    }

    public function actualizar(Institucion $institucion, array $entrada, Usuario $docente): array
    {
        $this->verificarPropietario($institucion, $docente);
        $datos = $this->validar($entrada);
        $this->instituciones->guardar($institucion, $datos);
        return $this->serializar($institucion->fresh());
    }

    public function eliminar(Institucion $institucion, Usuario $docente): void
    {
        $this->verificarPropietario($institucion, $docente);

        // Eliminar en cascada: asistencias → sesiones → grupo_alumnos → grupos → rubros → institución
        $grupos = \App\Models\Grupo::where('id_institucion', $institucion->id_institucion)->get();
        foreach ($grupos as $grupo) {
            $sesiones = \App\Models\Sesion::where('id_grupo', $grupo->id_grupo)->get();
            foreach ($sesiones as $sesion) {
                \App\Models\Asistencia::where('id_sesion', $sesion->id_sesion)->delete();
            }
            \App\Models\Sesion::where('id_grupo', $grupo->id_grupo)->delete();
            \App\Models\GrupoAlumno::where('id_grupo', $grupo->id_grupo)->delete();
        }
        \App\Models\Grupo::where('id_institucion', $institucion->id_institucion)->delete();
        \App\Models\RubroEvaluacion::where('id_institucion', $institucion->id_institucion)->delete();

        $this->instituciones->eliminar($institucion);
    }

    private function verificarPropietario(Institucion $institucion, Usuario $docente): void
    {
        if ($institucion->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException('No tienes permiso para acceder a esta institución.');
        }
    }

    private function validar(array $entrada): array
    {
        $validator = Validator::make($entrada, [
            'nombre' => ['required', 'string', 'max:150'],
            'logo'   => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function serializar(Institucion $institucion): array
    {
        return [
            'id_institucion' => $institucion->id_institucion,
            'id_docente'     => $institucion->id_docente,
            'nombre'         => $institucion->nombre,
            'logo'           => $institucion->logo,
            'created_at'     => $institucion->created_at?->toIso8601String(),
        ];
    }
}