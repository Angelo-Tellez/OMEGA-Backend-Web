{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/reportes/pdf.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
{--
 * ============================================================
 * Vista Blade — PDF de reporte de asistencias
 * Modulo: Reportes
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        h1 { font-size: 16px; color: #2C3E6B; margin-bottom: 2px; }
        .subtitle { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2C3E6B; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background: #f5f7fa; }
        .badge-ok  { color: #16a34a; font-weight: bold; }
        .badge-bad { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 9px; color: #999; text-align: right; }
    </style>
</head>
<body>
    <h1>Reporte de Asistencia — {{ $grupo->nombre }}</h1>
    <div class="subtitle">
        {{ $grupo->materia }} · {{ $grupo->periodo }} · Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Alumno</th>
                <th>Correo</th>
                <th>Presentes</th>
                <th>Ausentes</th>
                <th>Justificadas</th>
                <th>Total</th>
                <th>% Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($alumnos as $i => $datos)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $datos['alumno']->ap_pat }} {{ $datos['alumno']->ap_mat }}, {{ $datos['alumno']->nombre }}</td>
                    <td>{{ $datos['alumno']->email }}</td>
                    <td>{{ $datos['presentes'] }}</td>
                    <td>{{ $datos['ausentes'] }}</td>
                    <td>{{ $datos['justif'] }}</td>
                    <td>{{ $datos['total'] }}</td>
                    <td class="{{ $datos['porcentaje'] >= 80 ? 'badge-ok' : 'badge-bad' }}">
                        {{ $datos['porcentaje'] }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        OMEGA — Sistema de Control de Asistencias · {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>
