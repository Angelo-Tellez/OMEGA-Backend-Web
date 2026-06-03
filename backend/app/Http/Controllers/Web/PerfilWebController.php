<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Http\Requests\Web\UpdatePerfilRequest;
use App\Http\Requests\Web\UpdateContraseniaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Controlador Web — Perfil del Docente autenticado.
 * @version 1.0.0
 */
class PerfilWebController extends Controller
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {}

    public function index()
    {
        $usuario = Auth::user();
        return view('modules.perfil.index', compact('usuario'));
    }

    public function actualizar(UpdatePerfilRequest $request)
    {
        $usuario = Auth::user();

        $validated = $request->validated();

        $this->usuarios->guardar($usuario, [
            'nombre' => $request->nombre,
            'ap_pat' => $request->ap_pat,
            'ap_mat' => $request->ap_mat,
            'email'  => $request->email,
        ]);

        return redirect()->route('ca.perfil.index')
            ->with('success', 'La información se actualizó correctamente');
    }

    public function cambiarContrasenia(UpdateContraseniaRequest $request)
    {
        $usuario = Auth::user();

        if (!Hash::check($request->contrasenia_actual, $usuario->contrasenia)) {
            throw ValidationException::withMessages([
                'contrasenia_actual' => 'La contraseña actual no es correcta',
            ]);
        }

        $this->usuarios->guardar($usuario, [
            'contrasenia' => $request->contrasenia_nueva,
        ]);

        return redirect()->route('ca.perfil.index')
            ->with('success', 'La información se actualizó correctamente');
    }
}