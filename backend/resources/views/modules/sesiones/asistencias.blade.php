{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/sesiones/asistencias.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.app')
@section('title', 'Asistencias')
@section('content')

{{-- Breadcrumb y título --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ca.grupos.index') }}"
               class="text-sm font-body text-omg-nile-light hover:underline">Mis Aulas</a>
            <i class="fa-solid fa-chevron-right text-omg-kashmir text-xs"></i>
            <a href="{{ route('ca.grupos.sesiones', $sesion->grupo->id_grupo) }}"
               class="text-sm font-body text-omg-nile-light hover:underline">
                {{ $sesion->grupo->nombre }}
            </a>
            <i class="fa-solid fa-chevron-right text-omg-kashmir text-xs"></i>
            <span class="text-sm font-body text-omg-dark">Sesión {{ $sesion->fec_sesion->format('d/m/Y') }}</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">
            Asistencias — {{ $sesion->fec_sesion->format('d/m/Y') }}
        </h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            {{ $sesion->grupo->materia }} ·
            Apertura: {{ $sesion->hora_apertura->format('H:i') }} ·
            @if($sesion->hora_cierre)
                Cierre: {{ $sesion->hora_cierre->format('H:i') }}
            @else
                <span class="text-green-600">Sesión activa</span>
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('ca.dashboard.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a href="javascript:history.back()"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Resumen --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-circle-check text-green-600"></i>
        </div>
        <div>
            <p class="text-xl font-heading font-semibold text-omg-nile">
                {{ $asistencias->where('est_asistencia', 1)->count() }}
            </p>
            <p class="text-xs font-body text-omg-kashmir">Presentes</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-circle-xmark text-red-500"></i>
        </div>
        <div>
            <p class="text-xl font-heading font-semibold text-omg-nile">
                {{ $asistencias->where('est_asistencia', 2)->count() }}
            </p>
            <p class="text-xs font-body text-omg-kashmir">Ausentes</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-omg-chardon rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-file-circle-check text-omg-nile"></i>
        </div>
        <div>
            <p class="text-xl font-heading font-semibold text-omg-nile">
                {{ $asistencias->where('est_asistencia', 3)->count() }}
            </p>
            <p class="text-xs font-body text-omg-kashmir">Justificadas</p>
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-omg-kashmir-dark bg-omg-chardon">
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Alumno</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Correo</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Hora registro</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-omg-kashmir-dark">
            @forelse ($asistencias as $asistencia)
                <tr class="hover:bg-omg-chardon transition-colors">
                    <td class="px-5 py-4">
                        <p class="text-sm font-body font-semibold text-omg-dark">
                            {{ $asistencia->alumno->nombre }}
                            {{ $asistencia->alumno->ap_pat }}
                            {{ $asistencia->alumno->ap_mat }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-kashmir">
                            {{ $asistencia->alumno->email }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-dark">
                            {{ $asistencia->hora_registro?->format('H:i:s') ?? '—' }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        @if ($asistencia->est_asistencia === 1)
                            <span class="bg-green-100 text-green-700 text-xs font-body px-2 py-1 rounded-full">
                                Presente
                            </span>
                        @elseif ($asistencia->est_asistencia === 2)
                            <span class="bg-red-100 text-red-600 text-xs font-body px-2 py-1 rounded-full">
                                Ausente
                            </span>
                        @else
                            <span class="bg-omg-pastel text-omg-nile text-xs font-body px-2 py-1 rounded-full">
                                Justificada
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center">
                        <i class="fa-solid fa-users text-omg-kashmir fa-2x mb-3"></i>
                        <p class="text-sm font-body text-omg-kashmir">No se encontraron registros</p>
                        <p class="text-xs font-body text-omg-kashmir mt-1">
                            Aún no hay alumnos registrados en esta sesión
                        </p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection