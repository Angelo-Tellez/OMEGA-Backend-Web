<?php

/*
 * ============================================================
 * Servicio — Logica de negocio de grupos.
 * MPL-OMEGA-05 §2.3 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Services;

use App\Models\Grupo;
use App\Models\Usuario;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GrupoService
{
    public function __construct(
        private readonly GrupoRepositoryInterface $grupos
    ) {}

    public function listar(Usuario $docente): array
    {
        return $this->grupos->todosPorDocente($docente->id_usuario)
            ->map(fn(Grupo $g) => $this->serializar($g))
            ->values()
            ->all();
    }

    public function listarPorInstitucion(int $idInstitucion, Usuario $docente): array
    {
        return $this->grupos->todosPorDocente($docente->id_usuario)
            ->filter(fn(Grupo $g) => $g->id_institucion === $idInstitucion)
            ->map(fn(Grupo $g) => $this->serializar($g))
            ->values()
            ->all();
    }

    public function obtener(Grupo $grupo, Usuario $docente): array
    {
        $this->verificarPropietario($grupo, $docente);
        return $this->serializar($grupo);
    }

    public function crear(array $entrada, Usuario $docente): array
    {
        $datos = $this->validar($entrada);
        $datos['id_docente'] = $docente->id_usuario;
        $grupo = $this->grupos->crear($datos);
        return $this->serializar($grupo);
    }

    public function actualizar(Grupo $grupo, array $entrada, Usuario $docente): array
    {
        $this->verificarPropietario($grupo, $docente);
        $datos = $this->validar($entrada);
        $this->grupos->guardar($grupo, $datos);
        return $this->serializar($grupo->fresh());
    }

    public function eliminar(Grupo $grupo, Usuario $docente): void
    {
        $this->verificarPropietario($grupo, $docente);
        $this->grupos->eliminar($grupo);
    }

    public function generarCodigoInv(Grupo $grupo, Usuario $docente): array
    {
        $this->verificarPropietario($grupo, $docente);
        $codigo = strtoupper(Str::random(8));
        $this->grupos->guardar($grupo, ['codigo_inv' => $codigo]);
        return $this->serializar($grupo->fresh());
    }

    private function verificarPropietario(Grupo $grupo, Usuario $docente): void
    {
        if ($grupo->id_docente !== $docente->id_usuario) {
            throw new AuthorizationException('No tienes permiso para acceder a este grupo.');
        }
    }

    private function validar(array $entrada): array
    {
        // Si la app manda horario como string, convertirlo a array simple
        if (isset($entrada['horario']) && is_string($entrada['horario'])) {
            $horarioStr = trim($entrada['horario']);
            if (!empty($horarioStr)) {
                $entrada['horario'] = [['texto' => $horarioStr]];
            } else {
                $entrada['horario'] = null;
            }
        }

        // Construir el array de horario desde los campos del formulario (web)
        if (isset($entrada['horario_dias'])) {
            $horario  = [];
            $diasUsados = [];
            foreach ($entrada['horario_dias'] as $i => $dia) {
                $hi = $entrada['horario_inicio'][$i] ?? null;
                $hf = $entrada['horario_fin'][$i] ?? null;
                if (!$dia || !$hi || !$hf) continue;
                if (in_array($dia, $diasUsados)) continue;
                $diasUsados[] = $dia;
                $horario[] = [
                    'dia'         => $dia,
                    'hora_inicio' => $hi,
                    'hora_fin'    => $hf,
                ];
            }
            $entrada['horario'] = !empty($horario) ? $horario : null;
        }

        // Campos opcionales de la app móvil
        if (!isset($entrada['no_alumnos'])) {
            $entrada['no_alumnos'] = 30; // valor por defecto
        }
        if (!isset($entrada['id_institucion'])) {
            $entrada['id_institucion'] = $entrada['institucion_id'] ?? null;
        }

        $validator = Validator::make($entrada, [
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id_institucion'],
            'nombre'         => ['required', 'string', 'max:100'],
            'materia'        => ['required', 'string', 'max:150'],
            'periodo'        => ['required', 'string', 'max:50'],
            'no_alumnos'     => ['required', 'integer', 'min:1'],
            'horario'        => ['nullable', 'array'],
        ], [
            'horario.required' => 'Debes agregar al menos un día de clases',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function serializar(Grupo $grupo): array
    {
        return [
            'id_grupo'       => $grupo->id_grupo,
            'id_institucion' => $grupo->id_institucion,
            'id_docente'     => $grupo->id_docente,
            'nombre'         => $grupo->nombre,
            'materia'        => $grupo->materia,
            'periodo'        => $grupo->periodo,
            'no_alumnos'     => $grupo->no_alumnos,
            'codigo_inv'     => $grupo->codigo_inv,
            'created_at'     => $grupo->created_at?->toIso8601String(),
        ];
    }
}