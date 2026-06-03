{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/grupos/alumnos.blade.php
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
 * Vista Blade — Lista de alumnos de un grupo
 * Modulo: Grupos
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}
@extends('layouts.app')
@section('title', 'Alumnos — ' . $grupo->nombre)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm font-body text-omg-kashmir mb-1">
            <a href="{{ route('ca.dashboard.index') }}" class="hover:text-omg-nile">Inicio</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>{{ $grupo->nombre }} — {{ $grupo->materia }}</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Alumnos del Grupo</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            {{ $grupo->nombre }} · {{ $grupo->materia }} · {{ $grupo->periodo }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="bg-omg-pastel text-omg-nile text-sm font-body px-3 py-1.5 rounded-lg">
            {{ $alumnos->count() }} / {{ $grupo->no_alumnos }} alumnos
        </span>
        <a href="{{ route('ca.dashboard.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Volver
        </a>
    </div>
</div>

@if (session('success'))
    <div class="flex items-center gap-3 bg-white border border-green-200 rounded-lg px-4 py-3 mb-6">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        <p class="text-sm font-body text-omg-dark">{{ session('success') }}</p>
    </div>
@endif

<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-omg-kashmir-dark bg-omg-chardon">
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">#</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Alumno</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Correo</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Inscripción</th>
                <th class="text-right px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-omg-kashmir-dark">
            @forelse ($alumnos as $i => $ga)
                <tr class="hover:bg-omg-chardon transition-colors">
                    <td class="px-5 py-4 text-sm font-body text-omg-kashmir">{{ $i + 1 }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-omg-nile flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-heading font-bold text-white">
                                    {{ strtoupper(substr($ga->alumno->nombre, 0, 1) . substr($ga->alumno->ap_pat, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-body font-semibold text-omg-dark">
                                    {{ $ga->alumno->ap_pat }} {{ $ga->alumno->ap_mat }}, {{ $ga->alumno->nombre }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-xs font-body text-omg-kashmir">{{ $ga->alumno->email }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-xs font-body text-omg-kashmir">
                            {{ $ga->fec_inscripcion ? \Carbon\Carbon::parse($ga->fec_inscripcion)->format('d/m/Y') : '—' }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end">
                            <div x-data="{ open: false }">
                                <button type="button" @click="open = true"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-500 hover:text-white text-red-500 rounded-lg text-xs font-body transition-colors">
                                    <i class="fa-solid fa-user-minus"></i>
                                    Eliminar
                                </button>
                                <div x-show="open" x-transition class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                                    <div class="bg-white rounded-2xl p-6 max-w-xs w-full mx-4 shadow-xl">
                                        <p class="text-sm font-heading font-semibold text-omg-nile mb-2">¿Eliminar alumno del grupo?</p>
                                        <p class="text-xs font-body text-omg-kashmir mb-4">Se eliminará a <strong>{{ $ga->alumno->nombre }}</strong> del grupo. Esta acción no se puede deshacer.</p>
                                        <div class="flex gap-3">
                                            <button @click="open = false" class="flex-1 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm">Cancelar</button>
                                            <form method="POST" action="{{ route('ca.grupos.alumnos.destroy', [$grupo, $ga]) }}" class="flex-1">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-full py-2 bg-red-500 text-white font-heading font-semibold rounded-lg text-sm">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center">
                        <i class="fa-solid fa-users text-omg-kashmir fa-2x mb-3"></i>
                        <p class="text-sm font-body text-omg-kashmir">No hay alumnos inscritos en este grupo</p>
                        <p class="text-xs font-body text-omg-kashmir mt-1">
                            Los alumnos se unen con el código de invitación desde la app
                        </p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
