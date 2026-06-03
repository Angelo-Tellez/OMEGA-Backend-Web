{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/grupos/index.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.app')
@section('title', 'Mis Aulas')
@section('content')

{{-- Título y botón --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Mis Aulas</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            Gestiona tus grupos y genera códigos de invitación
        </p>
    </div>
    <a href="{{ route('ca.grupos.create') }}"
       class="flex items-center gap-2 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold px-4 py-2.5 rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-plus"></i>
        Nueva aula
    </a>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-omg-kashmir-dark bg-omg-chardon">
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Grupo</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Materia</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Periodo</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Alumnos</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Código</th>
                <th class="text-right px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-omg-kashmir-dark">
            @forelse ($grupos as $grupo)
                <tr class="hover:bg-omg-chardon transition-colors">
                    <td class="px-5 py-4">
                        <p class="text-sm font-body font-semibold text-omg-dark">{{ $grupo->nombre }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-dark">{{ $grupo->materia }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-kashmir">{{ $grupo->periodo }}</p>
                        <p class="text-xs font-body text-omg-kashmir mt-0.5">
                            <i class="fa-solid fa-users mr-1"></i>
                            {{ $grupo->grupoAlumnos()->count() }} / {{ $grupo->no_alumnos }} inscritos
                        </p>
                        @if ($grupo->horario && count($grupo->horario) > 0)
                            <p class="text-xs font-body text-omg-kashmir mt-0.5">
                                <i class="fa-regular fa-clock mr-1"></i>
                                @foreach ($grupo->horario as $h)
                                    @if (isset($h['dia']))
                                        <span>{{ ['L'=>'Lun','M'=>'Mar','X'=>'Mié','J'=>'Jue','V'=>'Vie','S'=>'Sáb','D'=>'Dom'][$h['dia']] ?? $h['dia'] }}
                                        {{ $h['hora_inicio'] }}–{{ $h['hora_fin'] }}</span>@if (!$loop->last), @endif
                                    @elseif (isset($h['texto']))
                                        <span>{{ $h['texto'] }}</span>
                                    @endif
                                @endforeach
                            </p>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-dark">{{ $grupo->no_alumnos }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @if ($grupo->codigo_inv)
                            <span class="bg-omg-chardon text-omg-nile font-heading font-semibold text-xs px-2 py-1 rounded-lg">
                                {{ $grupo->codigo_inv }}
                            </span>
                        @else
                            <form method="POST" action="{{ route('ca.grupos.codigo-inv', $grupo->id_grupo) }}">
                                @csrf
                                <button type="submit"
                                    class="text-xs font-body text-omg-nile-light hover:underline">
                                    <i class="fa-solid fa-rotate-right me-1"></i>Generar
                                </button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('ca.grupos.sesiones', $grupo->id_grupo) }}"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-nile hover:bg-omg-nile-dark text-white rounded-lg text-xs font-body transition-colors">
                                <i class="fa-solid fa-calendar-day"></i>
                                Sesiones
                            </a>
                            <a href="{{ route('ca.grupos.edit', $grupo->id_grupo) }}"
                               class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                                Editar
                            </a>
                            {{-- RF-61 Cerrar periodo --}}
                            <div x-data="{ open: false }">
                                <button @click="open = true"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-500 hover:text-white text-red-600 rounded-lg text-xs font-body transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                    Eliminar
                                </button>
                                <div
                                        x-show="open"
                                        x-cloak
                                        x-transition
                                        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <i class="fa-solid fa-trash text-red-500"></i>
                                            </div>
                                            <p class="text-sm font-heading font-semibold text-omg-nile">¿Eliminar grupo?</p>
                                        </div>
                                        <p class="text-xs font-body text-omg-kashmir mb-4">
                                            Se eliminarán <strong>completamente</strong> el grupo
                                            <strong>{{ $grupo->nombre }} — {{ $grupo->materia }}</strong>,
                                            junto con todas sus sesiones, asistencias e inscripciones de alumnos.
                                            <span class="text-red-500 font-semibold">Esta acción no se puede deshacer.</span>
                                        </p>
                                        <div class="flex gap-3">
                                            <button @click="open = false"
                                                class="flex-1 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm">
                                                Cancelar
                                            </button>
                                            <form method="POST" action="{{ route('ca.grupos.cerrar-periodo', $grupo->id_grupo) }}" class="flex-1">
                                                @csrf
                                                <button type="submit"
                                                    class="w-full py-2 bg-red-600 hover:bg-red-700 text-white font-heading font-semibold rounded-lg text-sm">
                                                    Eliminar
                                                </button>
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
                    <td colspan="6" class="px-5 py-12 text-center">
                        <i class="fa-solid fa-chalkboard-user text-omg-kashmir fa-2x mb-3"></i>
                        <p class="text-sm font-body text-omg-kashmir">No se encontraron registros</p>
                        <p class="text-xs font-body text-omg-kashmir mt-1">
                            Crea tu primera aula con el botón de arriba
                        </p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection