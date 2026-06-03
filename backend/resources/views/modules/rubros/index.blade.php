{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/rubros/index.blade.php
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
 * Vista Blade — Gestion de rubros de evaluacion
 * Modulo: Rubros
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}
@extends('layouts.app')
@section('title', 'Rubros de Evaluación')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm font-body text-omg-kashmir mb-1">
            <a href="{{ route('ca.instituciones.index') }}" class="hover:text-omg-nile">Mis Instituciones</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>{{ $institucion->nombre }}</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Rubros de Evaluación</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            Configura los porcentajes mínimos de asistencia para {{ $institucion->nombre }}
        </p>
    </div>
    <a href="{{ route('ca.instituciones.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

{{-- Formulario nuevo rubro --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 mb-6 max-w-lg">
    <h2 class="text-sm font-heading font-semibold text-omg-nile mb-4">Agregar rubro</h2>

    @if ($errors->any())
        <div class="flex items-start gap-3 bg-white border border-red-200 rounded-lg px-4 py-3 mb-4">
            <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
            <ul class="text-sm text-omg-dark space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('ca.rubros.store', $institucion->id_institucion) }}"
          class="flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-body text-omg-kashmir mb-1">Nombre del rubro</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}"
                   placeholder="Ej: Ordinario, Extraordinario"
                   class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir"/>
        </div>
        <div class="w-32">
            <label class="block text-xs font-body text-omg-kashmir mb-1">% mínimo</label>
            <input type="number" name="porcentaje_minimo" value="{{ old('porcentaje_minimo') }}"
                   min="0" max="100" step="0.5" placeholder="80"
                   class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir"/>
        </div>
        <button type="submit"
            class="flex items-center gap-2 px-4 py-2 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold rounded-lg text-sm transition-colors">
            <i class="fa-solid fa-plus"></i>
            Agregar
        </button>
    </form>
</div>

{{-- Lista de rubros --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden max-w-lg">

    {{-- Encabezado --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-omg-chardon border-b border-omg-kashmir-dark">
        <span class="flex-1 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Rubro</span>
        <span class="w-20 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide text-center">% Mín.</span>
        <span class="w-28 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide text-right">Acciones</span>
    </div>

    <div class="divide-y divide-omg-kashmir-dark">
        @forelse ($rubros as $rubro)
            <div class="px-5 py-3 hover:bg-omg-chardon transition-colors"
                 x-data="{ editando: false, nombre: '{{ addslashes($rubro->nombre) }}', pct: {{ $rubro->porcentaje_minimo }} }">

                {{-- Vista normal --}}
                <div x-show="!editando" class="flex items-center gap-3">
                    <span class="flex-1 text-sm font-body font-semibold text-omg-dark" x-text="nombre"></span>
                    <span class="w-20 text-center">
                        <span class="bg-omg-pastel text-omg-nile text-xs font-body px-2 py-1 rounded-full font-semibold"
                              x-text="pct + '%'"></span>
                    </span>
                    <div class="w-28 flex items-center justify-end gap-1.5">
                        <button @click="editando = true"
                            class="flex items-center gap-1 px-2.5 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                            <i class="fa-regular fa-pen-to-square"></i> Editar
                        </button>
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = true"
                                class="flex items-center px-2.5 py-1.5 bg-red-50 hover:bg-red-500 hover:text-white text-red-500 rounded-lg text-xs font-body transition-colors">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <div x-show="open" x-transition class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                                <div class="bg-white rounded-2xl p-6 max-w-xs w-full mx-4 shadow-xl">
                                    <p class="text-sm font-heading font-semibold text-omg-nile mb-2">¿Eliminar rubro?</p>
                                    <p class="text-xs font-body text-omg-kashmir mb-4">Esta acción no se puede deshacer.</p>
                                    <div class="flex gap-3">
                                        <button @click="open = false" class="flex-1 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm">Cancelar</button>
                                        <form method="POST" action="{{ route('ca.rubros.destroy', [$institucion->id_institucion, $rubro->id_rubro]) }}" class="flex-1">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full py-2 bg-red-500 text-white font-heading font-semibold rounded-lg text-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vista edición --}}
                <div x-show="editando">
                    <form method="POST" action="{{ route('ca.rubros.update', [$institucion->id_institucion, $rubro->id_rubro]) }}">
                        @csrf @method('PUT')
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="text" name="nombre" x-model="nombre" required
                                   placeholder="Nombre"
                                   class="flex-1 min-w-0 px-3 py-1.5 border border-omg-nile rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                            <input type="number" name="porcentaje_minimo" x-model="pct"
                                   min="0" max="100" step="0.5" required
                                   placeholder="%"
                                   class="w-20 px-3 py-1.5 border border-omg-nile rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                            <button type="button" @click="editando = false; nombre = '{{ addslashes($rubro->nombre) }}'; pct = {{ $rubro->porcentaje_minimo }}"
                                class="flex items-center gap-1 px-2.5 py-1.5 bg-omg-chardon text-omg-nile rounded-lg text-xs font-body hover:bg-omg-pastel transition-colors whitespace-nowrap">
                                <i class="fa-solid fa-xmark"></i> Cancelar
                            </button>
                            <button type="submit"
                                class="flex items-center gap-1 px-2.5 py-1.5 bg-omg-coral text-white rounded-lg text-xs font-body hover:bg-omg-coral-dark transition-colors whitespace-nowrap">
                                <i class="fa-solid fa-check"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <i class="fa-solid fa-chart-pie text-omg-kashmir fa-2x mb-3"></i>
                <p class="text-sm font-body text-omg-kashmir">No hay rubros configurados</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
