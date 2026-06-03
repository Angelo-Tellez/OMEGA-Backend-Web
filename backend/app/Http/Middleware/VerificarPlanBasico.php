<?php

/*
 * ============================================================
 * Middleware — Verificacion de plan activo del docente.
 * MPL-OMEGA-05 §6.1
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Middleware;

use App\Services\SuscripcionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware — Restricciones del Plan Básico.
 * Plan Básico: 1 institución, 1 grupo por institución, sin reportes.
 * Plan Mensual: sin restricciones.
 */
class VerificarPlanBasico
{
    public function __construct(
        private readonly SuscripcionService $suscripciones,
    ) {}

    public function handle(Request $request, Closure $next, string $recurso = '')
    {
        $docente     = Auth::user();
        $suscripcion = $this->suscripciones->obtener($docente);

        // Plan mensual activo o en gracia → sin restricciones
        if ($suscripcion['plan'] === 2 && in_array($suscripcion['est_suscripcion'], [1, 3])) {
            return $next($request);
        }

        // Plan básico — verificar límites según el recurso
        match ($recurso) {
            'institucion' => $this->verificarInstitucion($docente),
            'grupo'       => $this->verificarGrupo($docente, $request),
            'reportes'    => $this->verificarReportes(),
            default       => null,
        };

        return $next($request);
    }

    private function verificarInstitucion($docente): void
    {
        $total = \App\Models\Institucion::where('id_docente', $docente->id_usuario)->count();
        if ($total >= 1) {
            abort(403, 'El Plan Básico solo permite 1 institución. Actualiza al Plan Mensual para agregar más.');
        }
    }

    private function verificarGrupo($docente, Request $request): void
    {
        $idInstitucion = $request->input('id_institucion')
            ?? session('institucion_id');

        if (!$idInstitucion) return;

        $total = \App\Models\Grupo::where('id_institucion', $idInstitucion)
            ->where('id_docente', $docente->id_usuario)
            ->count();

        if ($total >= 1) {
            abort(403, 'El Plan Básico solo permite 1 grupo por institución. Actualiza al Plan Mensual para agregar más.');
        }
    }

    private function verificarReportes(): void
    {
        abort(403, 'Los reportes están disponibles en el Plan Mensual. Actualiza tu plan para acceder.');
    }
}
