<?php

/*
 * ============================================================
 * Servicio — Logica de negocio de usuarios.
 * MPL-OMEGA-05 §2.3 | §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Services;

use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsuarioService
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios
    ) {}

    public function listar(): array
    {
        return $this->usuarios->todos()
            ->map(fn(Usuario $u) => $this->serializar($u))
            ->values()
            ->all();
    }

    public function obtener(Usuario $usuario): array
    {
        return $this->serializar($usuario);
    }

    public function crear(array $entrada): array
    {
        $datos = $this->validarCreacion($entrada);
        $usuario = $this->usuarios->crear($datos);
        return $this->serializar($usuario);
    }

    public function actualizar(Usuario $usuario, array $entrada): array
    {
        $datos = $this->validarActualizacion($entrada, $usuario->id_usuario);
        $this->usuarios->guardar($usuario, $datos);
        return $this->serializar($usuario->fresh());
    }

    public function eliminar(Usuario $usuario): void
    {
        $this->usuarios->eliminar($usuario);
    }

    private function serializar(Usuario $usuario): array
    {
        return [
            'id_usuario'  => $usuario->id_usuario,
            'nombre'      => $usuario->nombre,
            'ap_pat'      => $usuario->ap_pat,
            'ap_mat'      => $usuario->ap_mat,
            'email'       => $usuario->email,
            'rol'         => $usuario->rol,
            'created_at'  => $usuario->created_at?->toIso8601String(),
        ];
    }

    private function validarCreacion(array $entrada): array
    {
        $validator = Validator::make($entrada, [
            'nombre'      => ['required', 'string', 'max:100'],
            'ap_pat'      => ['required', 'string', 'max:100'],
            'ap_mat'      => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:200', 'unique:usuarios,email'],
            'contrasenia' => ['required', 'string', 'min:8'],
            'rol'         => ['required', 'integer', 'in:1,2'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function validarActualizacion(array $entrada, int $id): array
    {
        $validator = Validator::make($entrada, [
            'nombre'      => ['sometimes', 'required', 'string', 'max:100'],
            'ap_pat'      => ['sometimes', 'required', 'string', 'max:100'],
            'ap_mat'      => ['sometimes', 'required', 'string', 'max:100'],
            'email'       => ['sometimes', 'required', 'email', 'max:200', 'unique:usuarios,email,' . $id . ',id_usuario'],
            'contrasenia' => ['sometimes', 'required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}