<?php

/*
 * ============================================================
 * Controlador Web — Gestión de Periodos por Institución.
 * MPL-OMEGA-05 §6.1 | §6.2
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador Web — Gestión de periodos académicos por institución.
 */
class PeriodoWebController extends Controller
{
    public function index(int $idInstitucion)
    {
        $institucion = Institucion::findOrFail($idInstitucion);
        abort_if($institucion->id_docente !== Auth::user()->id_usuario, 403);

        $periodos = Periodo::where('id_institucion', $idInstitucion)
            ->orderByDesc('created_at')->get();

        return view('modules.periodos.index', compact('institucion', 'periodos'));
    }

    public function store(Request $request, int $idInstitucion)
    {
        $institucion = Institucion::findOrFail($idInstitucion);
        abort_if($institucion->id_docente !== Auth::user()->id_usuario, 403);

        // Ruta rápida: opciones predefinidas (Ene-Jun 2026, etc.)
        if ($request->filled('nombre_rapido')) {
            $request->validate([
                'nombre_rapido' => ['required', 'string', 'max:100'],
            ]);
            $nombre = trim($request->nombre_rapido);
        } else {
            // Ruta personalizada: intervalo de fechas → nombre generado
            $anioActual    = now()->year;
            $anioSiguiente = $anioActual + 1;

            $request->validate([
                'fecha_inicio' => [
                    'required', 'date',
                    "after_or_equal:{$anioActual}-01-01",
                    "before_or_equal:{$anioSiguiente}-12-31",
                ],
                'fecha_fin' => [
                    'required', 'date',
                    'after_or_equal:fecha_inicio',
                    "before_or_equal:{$anioSiguiente}-12-31",
                ],
            ], [
                'fecha_inicio.required'        => 'La fecha de inicio es obligatoria.',
                'fecha_inicio.after_or_equal'  => 'La fecha de inicio debe estar dentro del año actual o siguiente.',
                'fecha_inicio.before_or_equal' => 'La fecha de inicio debe estar dentro del año actual o siguiente.',
                'fecha_fin.required'           => 'La fecha de término es obligatoria.',
                'fecha_fin.after_or_equal'     => 'La fecha de término debe ser igual o posterior a la fecha de inicio.',
                'fecha_fin.before_or_equal'    => 'La fecha de término debe estar dentro del año actual o siguiente.',
            ]);

            $nombre = $this->generarNombre($request->fecha_inicio, $request->fecha_fin);
        }

        $existe = Periodo::where('id_institucion', $idInstitucion)
            ->whereRaw('LOWER(nombre) = ?', [strtolower($nombre)])
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->with('error', 'El periodo "' . $nombre . '" ya está registrado');
        }

        Periodo::create([
            'id_institucion' => $idInstitucion,
            'nombre'         => $nombre,
            'activo'         => true,
        ]);

        return redirect()->route('ca.periodos.index', $idInstitucion)
            ->with('success', 'Periodo "' . $nombre . '" agregado correctamente');
    }

    public function update(Request $request, int $idInstitucion, Periodo $periodo)
    {
        abort_if($periodo->id_institucion !== $idInstitucion, 403);

        $anioActual    = now()->year;
        $anioSiguiente = $anioActual + 1;

        $request->validate([
            'fecha_inicio' => [
                'required', 'date',
                "after_or_equal:{$anioActual}-01-01",
                "before_or_equal:{$anioSiguiente}-12-31",
            ],
            'fecha_fin' => [
                'required', 'date',
                'after_or_equal:fecha_inicio',
                "before_or_equal:{$anioSiguiente}-12-31",
            ],
        ], [
            'fecha_inicio.required'        => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.after_or_equal'  => 'La fecha de inicio debe estar dentro del año actual o siguiente.',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio debe estar dentro del año actual o siguiente.',
            'fecha_fin.required'           => 'La fecha de término es obligatoria.',
            'fecha_fin.after_or_equal'     => 'La fecha de término debe ser igual o posterior a la fecha de inicio.',
            'fecha_fin.before_or_equal'    => 'La fecha de término debe estar dentro del año actual o siguiente.',
        ]);

        $nombre = $this->generarNombre($request->fecha_inicio, $request->fecha_fin);

        $periodo->update(['nombre' => $nombre]);

        return redirect()->route('ca.periodos.index', $idInstitucion)
            ->with('success', 'Periodo actualizado a "' . $nombre . '" correctamente');
    }

    public function destroy(int $idInstitucion, Periodo $periodo)
    {
        abort_if($periodo->id_institucion !== $idInstitucion, 403);
        $periodo->delete();

        return redirect()->route('ca.periodos.index', $idInstitucion)
            ->with('success', 'Periodo eliminado correctamente');
    }

    /**
     * Genera el nombre del periodo a partir de un intervalo de fechas.
     * Ejemplo: 2026-01-15 → 2026-06-30  →  "Enero - Junio 2026"
     */
    private function generarNombre(string $inicio, string $fin): string
    {
        $meses  = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                   'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $fechaI = new \DateTime($inicio);
        $fechaF = new \DateTime($fin);
        $nomI   = $meses[(int) $fechaI->format('n') - 1];
        $nomF   = $meses[(int) $fechaF->format('n') - 1];
        $anioI  = $fechaI->format('Y');
        $anioF  = $fechaF->format('Y');

        return $anioI === $anioF
            ? "{$nomI} - {$nomF} {$anioI}"
            : "{$nomI} {$anioI} - {$nomF} {$anioF}";
    }
}
