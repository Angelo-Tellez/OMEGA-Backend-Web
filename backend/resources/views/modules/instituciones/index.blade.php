{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/instituciones/index.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
@extends('layouts.app')
@section('title', 'Mis Instituciones')
@section('content')

{{-- Título y botón --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Mis Instituciones</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            Gestiona los espacios donde impartes clases
        </p>
    </div>
    <a href="{{ route('ca.instituciones.create') }}"
       class="flex items-center gap-2 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold px-4 py-2.5 rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-plus"></i>
        Nueva institución
    </a>
</div>

@forelse ($instituciones as $item)
    @php $inst = $item['institucion']; $grupos = $item['grupos']; @endphp

    <div class="bg-white rounded-xl border border-omg-kashmir-dark mb-4 overflow-hidden"
         x-data="{ abierto: false }">

        {{-- Header institución --}}
        <div class="flex items-center gap-4 px-5 py-4 bg-omg-chardon"
             x-data="{
                seleccionada: {{ session('institucion_id') == $inst->id_institucion ? 'true' : 'false' }},
                cargando: false,
                async seleccionar() {
                    this.cargando = true;
                    const res = await fetch('{{ route('ca.instituciones.seleccionar', $inst->id_institucion) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.seleccionada = true;
                        window.dispatchEvent(new CustomEvent('inst-seleccionada', {
                            detail: { id: data.id, nombre: data.nombre }
                        }));
                        // Desmarcar otras instituciones en la misma página
                        document.querySelectorAll('[data-inst-badge]').forEach(el => {
                            if (el !== $el) el._x_dataStack[0].seleccionada = false;
                        });
                    }
                    this.cargando = false;
                }
             }"
             data-inst-badge>

            {{-- Logo --}}
            <div class="w-10 h-10 rounded-lg border border-omg-kashmir-dark bg-white flex items-center justify-center overflow-hidden shrink-0">
                @if ($inst->logo)
                    <img src="{{ $inst->logo }}" alt="{{ $inst->nombre }}" class="h-8 w-auto object-contain"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none" class="items-center justify-center w-full h-full">
                        <i class="fa-solid fa-building-columns text-omg-kashmir text-sm"></i>
                    </div>
                @else
                    <i class="fa-solid fa-building-columns text-omg-kashmir text-sm"></i>
                @endif
            </div>

            {{-- Nombre y contador --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-heading font-semibold text-omg-nile truncate">{{ $inst->nombre }}</p>
                <p class="text-xs font-body text-omg-kashmir">{{ $grupos->count() }} grupo(s)</p>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center gap-2 flex-wrap justify-end">
                {{-- Seleccionar / Activa --}}
                <template x-if="!seleccionada">
                    <button @click="seleccionar()" :disabled="cargando"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-coral hover:bg-omg-coral-dark text-white rounded-lg text-xs font-body transition-colors disabled:opacity-60">
                        <i class="fa-solid" :class="cargando ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                        <span x-text="cargando ? 'Guardando...' : 'Seleccionar'"></span>
                    </button>
                </template>
                <template x-if="seleccionada">
                    <span class="flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs font-body">
                        <i class="fa-solid fa-circle-check"></i> Activa
                    </span>
                </template>

                <a href="{{ route('ca.rubros.index', $inst->id_institucion) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-omg-nile hover:text-white text-omg-nile border border-omg-kashmir-dark rounded-lg text-xs font-body transition-colors">
                    <i class="fa-solid fa-chart-pie"></i> Rubros
                </a>
                <a href="{{ route('ca.periodos.index', $inst->id_institucion) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-omg-nile hover:text-white text-omg-nile border border-omg-kashmir-dark rounded-lg text-xs font-body transition-colors">
                    <i class="fa-solid fa-calendar-alt"></i> Periodos
                </a>
                <a href="{{ route('ca.instituciones.edit', $inst->id_institucion) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-omg-nile hover:text-white text-omg-nile border border-omg-kashmir-dark rounded-lg text-xs font-body transition-colors">
                    <i class="fa-regular fa-pen-to-square"></i> Editar
                </a>

                {{-- Eliminar --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = true"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-500 hover:text-white text-red-500 border border-red-200 rounded-lg text-xs font-body transition-colors">
                        <i class="fa-solid fa-delete-left"></i> Eliminar
                    </button>
                    <div x-show="open" x-transition class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl">
                            <p class="text-sm font-heading font-semibold text-omg-nile mb-2">¿Eliminar institución?</p>
                            <p class="text-xs font-body text-omg-kashmir mb-4">
                                Esta acción no se puede deshacer. Se eliminarán todos los datos asociados.
                            </p>
                            <div class="flex gap-3">
                                <button @click="open = false"
                                    class="flex-1 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm">
                                    Cancelar
                                </button>
                                <form method="POST"
                                      action="{{ route('ca.instituciones.destroy', $inst->id_institucion) }}"
                                      class="flex-1">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-full py-2 bg-red-500 hover:bg-red-600 text-white font-heading font-semibold rounded-lg text-sm">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Toggle grupos --}}
                <button @click="abierto = !abierto"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                    <i class="fa-solid fa-layer-group"></i>
                    <span x-text="abierto ? 'Ocultar grupos' : 'Ver grupos'"></span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                       :class="abierto ? 'rotate-180' : ''"></i>
                </button>
            </div>
        </div>

        {{-- Grupos (colapsados por defecto) --}}
        <div x-show="abierto" x-collapse>
            @if ($grupos->count() > 0)
                <div class="divide-y divide-omg-kashmir-dark">
                    @foreach ($grupos as $g)
                        @php $grupo = $g['grupo']; @endphp
                        <div class="flex items-center px-5 py-3 hover:bg-omg-chardon transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-body font-semibold text-omg-dark truncate">
                                    {{ $grupo->nombre }} — {{ $grupo->materia }}
                                </p>
                                <p class="text-xs font-body text-omg-kashmir">
                                    {{ $grupo->periodo }} · {{ $g['totalAlumnos'] }} alumno(s)
                                </p>
                            </div>
                            @if ($g['sesionActiva'])
                                <span class="bg-green-100 text-green-600 text-xs font-body px-2 py-0.5 rounded-full mr-3 animate-pulse shrink-0">
                                    EN VIVO
                                </span>
                            @endif
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('ca.instituciones.ir', [$inst->id_institucion, 'destino' => route('ca.grupos.sesiones', $grupo)]) }}"
                                   class="flex items-center gap-1 px-3 py-1.5 bg-omg-nile hover:bg-omg-nile-dark text-white rounded-lg text-xs font-body transition-colors">
                                    <i class="fa-solid fa-calendar-check"></i> Sesiones
                                </a>
                                <a href="{{ route('ca.instituciones.ir', [$inst->id_institucion, 'destino' => route('ca.grupos.alumnos', $grupo)]) }}"
                                   class="flex items-center gap-1 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                                    <i class="fa-solid fa-users"></i> Alumnos
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-6 text-center border-t border-omg-kashmir-dark">
                    <p class="text-sm font-body text-omg-kashmir">Sin grupos en esta institución</p>
                    <a href="{{ route('ca.grupos.create') }}"
                       class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 bg-omg-coral text-white rounded-lg text-xs font-body">
                        <i class="fa-solid fa-plus"></i> Crear grupo
                    </a>
                </div>
            @endif
        </div>
    </div>

@empty
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-12 text-center">
        <i class="fa-solid fa-building-columns text-omg-kashmir fa-2x mb-3"></i>
        <p class="text-sm font-body text-omg-kashmir">No se encontraron registros</p>
        <p class="text-xs font-body text-omg-kashmir mt-1">
            Crea tu primera institución con el botón de arriba
        </p>
    </div>
@endforelse

@endsection
