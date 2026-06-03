{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/periodos/index.blade.php
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
 * Vista Blade — Lista y gestion de periodos por institucion
 * Modulo: Periodos
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}
@extends('layouts.app')
@section('title', 'Periodos — ' . $institucion->nombre)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm font-body text-omg-kashmir mb-1">
            <a href="{{ route('ca.instituciones.index') }}" class="hover:text-omg-nile">Mis Instituciones</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>{{ $institucion->nombre }}</span>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>Periodos</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Periodos Académicos</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            Define los periodos de <strong>{{ $institucion->nombre }}</strong>. Al crear un aula podrás seleccionar uno de estos periodos.
        </p>
    </div>
    <a href="{{ route('ca.instituciones.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

{{-- Agregar periodo --}}
@php
    $periodosExistentes = $periodos->pluck('nombre')->map(fn($n) => strtolower(trim($n)))->toArray();
    $anioActual         = now()->year;
    $anioSiguiente      = $anioActual + 1;
    $hayErrorFecha      = $errors->has('fecha_inicio') || $errors->has('fecha_fin');
@endphp
<div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 mb-6"
     x-data="{
        mostrarPersonalizado: {{ $hayErrorFecha ? 'true' : 'false' }},
        existentes: JSON.parse('{{ addslashes(json_encode($periodosExistentes)) }}'),
        get opciones() {
            const anio = new Date().getFullYear();
            return [
                'Ene-Jun ' + anio,
                'Ago-Dic ' + anio,
                'Ene-Jun ' + (anio + 1),
                'Ago-Dic ' + (anio + 1),
            ];
        }
     }">
    <h2 class="text-sm font-heading font-semibold text-omg-nile mb-3">Agregar periodo</h2>

    {{-- Opciones rápidas --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <template x-for="op in opciones" :key="op">
            <span>
                <form x-show="!existentes.includes(op.toLowerCase())"
                      method="POST" action="{{ route('ca.periodos.store', $institucion->id_institucion) }}">
                    @csrf
                    {{-- Las opciones rápidas envían nombre directo como texto --}}
                    <input type="hidden" name="nombre_rapido" :value="op">
                    <button type="submit"
                            class="px-3 py-1.5 border rounded-lg text-xs font-body transition-colors bg-white text-omg-nile border-omg-kashmir hover:border-omg-nile hover:bg-omg-chardon"
                            x-text="op">
                    </button>
                </form>
                <span x-show="existentes.includes(op.toLowerCase())"
                      class="px-3 py-1.5 border border-omg-nile rounded-lg text-xs font-body bg-omg-nile text-white flex items-center gap-1">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span x-text="op"></span>
                </span>
            </span>
        </template>
    </div>

    {{-- Periodo personalizado con selector de fechas --}}
    <button type="button" @click="mostrarPersonalizado = !mostrarPersonalizado"
            class="text-xs font-body text-omg-nile hover:underline flex items-center gap-1 mb-3">
        <i class="fa-solid fa-plus text-xs" :class="mostrarPersonalizado ? 'rotate-45' : ''"></i>
        Agregar periodo personalizado
    </button>

    <div x-show="mostrarPersonalizado" x-transition
         x-data="{
             fechaInicio: '{{ old('fecha_inicio') }}',
             fechaFin:    '{{ old('fecha_fin') }}',
             get nombreGenerado() {
                 if (!this.fechaInicio || !this.fechaFin) return '';
                 const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                 const [anioI, mesI] = this.fechaInicio.split('-').map(Number);
                 const [anioF, mesF] = this.fechaFin.split('-').map(Number);
                 const nomI = meses[mesI - 1];
                 const nomF = meses[mesF - 1];
                 return anioI === anioF
                     ? `${nomI} - ${nomF} ${anioI}`
                     : `${nomI} ${anioI} - ${nomF} ${anioF}`;
             },
             get fechaFinMin() {
                 return this.fechaInicio || '{{ $anioActual }}-01-01';
             },
             get valido() {
                 return this.fechaInicio !== '' && this.fechaFin !== '' && this.fechaFin >= this.fechaInicio;
             }
         }">
        <form method="POST" action="{{ route('ca.periodos.store', $institucion->id_institucion) }}"
              class="space-y-3 p-4 bg-omg-chardon rounded-xl border border-omg-kashmir-dark">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-body text-omg-dark mb-1">
                        Fecha de inicio <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="fecha_inicio" x-model="fechaInicio" required
                           min="{{ $anioActual }}-01-01"
                           max="{{ $anioSiguiente }}-12-31"
                           class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile @error('fecha_inicio') border-red-400 @enderror"/>
                    @error('fecha_inicio')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-body text-omg-dark mb-1">
                        Fecha de término <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="fecha_fin" x-model="fechaFin" required
                           :min="fechaFinMin"
                           max="{{ $anioSiguiente }}-12-31"
                           class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile @error('fecha_fin') border-red-400 @enderror"/>
                    @error('fecha_fin')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Preview del nombre generado --}}
            <div x-show="nombreGenerado"
                 class="flex items-center gap-2 bg-white border border-omg-kashmir-dark rounded-lg px-3 py-2 text-xs font-body text-omg-dark">
                <i class="fa-solid fa-calendar-check text-omg-nile text-xs"></i>
                <span>Periodo: <strong x-text="nombreGenerado" class="text-omg-nile"></strong></span>
            </div>

            <p class="text-xs font-body text-omg-kashmir">
                Solo se permiten fechas entre <strong>{{ $anioActual }}-01-01</strong> y <strong>{{ $anioSiguiente }}-12-31</strong>.
            </p>

            <div class="flex items-center justify-end gap-2">
                <button type="button"
                        @click="mostrarPersonalizado = false; fechaInicio = ''; fechaFin = ''"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-omg-kashmir text-omg-kashmir rounded-lg text-xs font-body hover:bg-omg-chardon transition-colors">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button type="submit" :disabled="!valido"
                        :class="valido ? 'bg-omg-coral hover:bg-omg-coral-dark text-white' : 'bg-omg-chardon text-omg-kashmir cursor-not-allowed'"
                        class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-body transition-colors">
                    <i class="fa-solid fa-plus"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Lista de periodos --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden">

    {{-- Encabezado --}}
    <div class="flex items-center px-5 py-3 bg-omg-chardon border-b border-omg-kashmir-dark">
        <span class="flex-1 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Periodo</span>
        <span class="text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Acciones</span>
    </div>

    <div class="divide-y divide-omg-kashmir-dark">
        @forelse ($periodos as $periodo)
            <div class="px-5 py-3 hover:bg-omg-chardon transition-colors"
                 x-data="{ editando: false }">

                {{-- Vista normal --}}
                <div x-show="!editando" class="flex items-center gap-3">
                    <span class="flex-1 text-sm font-body font-semibold text-omg-dark">{{ $periodo->nombre }}</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editando = true"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                            <i class="fa-regular fa-pen-to-square"></i> Editar
                        </button>
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = true"
                                class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-500 hover:text-white text-red-500 rounded-lg text-xs font-body transition-colors">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                            <div x-show="open" x-transition class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                                <div class="bg-white rounded-2xl p-6 max-w-xs w-full mx-4 shadow-xl">
                                    <p class="text-sm font-heading font-semibold text-omg-nile mb-2">¿Eliminar periodo?</p>
                                    <p class="text-xs font-body text-omg-kashmir mb-4">
                                        Se eliminará <strong>{{ $periodo->nombre }}</strong>. Las aulas que ya usan este periodo no se verán afectadas.
                                    </p>
                                    <div class="flex gap-3">
                                        <button @click="open = false" class="flex-1 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm">Cancelar</button>
                                        <form method="POST" action="{{ route('ca.periodos.destroy', [$institucion->id_institucion, $periodo->id_periodo]) }}" class="flex-1">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full py-2 bg-red-500 text-white font-heading font-semibold rounded-lg text-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vista edición con selector de fechas --}}
                <div x-show="editando"
                     x-data="{
                         fechaInicio: '',
                         fechaFin: '',
                         get nombreGenerado() {
                             if (!this.fechaInicio || !this.fechaFin) return '';
                             const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                             const [anioI, mesI] = this.fechaInicio.split('-').map(Number);
                             const [anioF, mesF] = this.fechaFin.split('-').map(Number);
                             const nomI = meses[mesI - 1];
                             const nomF = meses[mesF - 1];
                             return anioI === anioF
                                 ? `${nomI} - ${nomF} ${anioI}`
                                 : `${nomI} ${anioI} - ${nomF} ${anioF}`;
                         },
                         get valido() {
                             return this.fechaInicio !== '' && this.fechaFin !== '' && this.fechaFin >= this.fechaInicio;
                         }
                     }">
                    <form method="POST" action="{{ route('ca.periodos.update', [$institucion->id_institucion, $periodo->id_periodo]) }}"
                          class="space-y-2">
                        @csrf @method('PATCH')

                        <p class="text-xs font-body text-omg-kashmir">
                            Periodo actual: <strong class="text-omg-nile">{{ $periodo->nombre }}</strong>
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-body text-omg-dark mb-1">
                                    Nueva fecha de inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="fecha_inicio" x-model="fechaInicio" required
                                       min="{{ $anioActual }}-01-01"
                                       max="{{ $anioSiguiente }}-12-31"
                                       class="w-full px-3 py-1.5 border border-omg-nile rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                            </div>
                            <div>
                                <label class="block text-xs font-body text-omg-dark mb-1">
                                    Nueva fecha de término <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="fecha_fin" x-model="fechaFin" required
                                       :min="fechaInicio || '{{ $anioActual }}-01-01'"
                                       max="{{ $anioSiguiente }}-12-31"
                                       class="w-full px-3 py-1.5 border border-omg-nile rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                            </div>
                        </div>

                        <div x-show="nombreGenerado"
                             class="flex items-center gap-2 bg-omg-chardon border border-omg-kashmir-dark rounded-lg px-3 py-1.5 text-xs font-body text-omg-dark">
                            <i class="fa-solid fa-calendar-check text-omg-nile text-xs"></i>
                            <span>Nuevo nombre: <strong x-text="nombreGenerado" class="text-omg-nile"></strong></span>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button"
                                    @click="editando = false; fechaInicio = ''; fechaFin = ''"
                                    class="flex items-center gap-1 px-2.5 py-1.5 bg-omg-chardon text-omg-nile rounded-lg text-xs font-body hover:bg-omg-pastel transition-colors whitespace-nowrap">
                                <i class="fa-solid fa-xmark"></i> Cancelar
                            </button>
                            <button type="submit" :disabled="!valido"
                                    :class="valido ? 'bg-omg-coral hover:bg-omg-coral-dark text-white' : 'bg-omg-chardon text-omg-kashmir cursor-not-allowed'"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-body transition-colors whitespace-nowrap">
                                <i class="fa-solid fa-check"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <i class="fa-solid fa-calendar text-omg-kashmir fa-2x mb-3"></i>
                <p class="text-sm font-body text-omg-kashmir">Sin periodos configurados</p>
                <p class="text-xs font-body text-omg-kashmir mt-1">Agrega el primero con el formulario de arriba</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
