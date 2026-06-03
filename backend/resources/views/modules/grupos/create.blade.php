{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/grupos/create.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.app')
@section('title', 'Nueva Aula')
@section('content')

{{-- Título --}}
<div class="mb-6">
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">Nueva Aula</h1>
    <p class="text-sm font-body text-omg-kashmir mt-1">
        Registra un nuevo grupo para controlar asistencias
    </p>
</div>

{{-- Formulario --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark p-6 max-w-lg">



    <form method="POST" action="{{ route('ca.grupos.store') }}" class="space-y-5"
>
        @csrf

        {{-- Institución --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Institución
            </label>
            {{-- Institución activa bloqueada (no editable) --}}
            <input type="hidden" name="id_institucion" value="{{ session('institucion_id') }}">
            <div class="w-full px-4 py-2.5 bg-omg-chardon border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark flex items-center justify-between">
                <span>{{ session('institucion_nombre', 'Sin institución') }}</span>
                <i class="fa-solid fa-lock text-omg-kashmir text-xs"></i>
            </div>
            <p class="text-xs font-body text-omg-kashmir mt-1">
                La aula se creará en la institución activa.
                <a href="{{ route('ca.instituciones.index') }}" class="text-omg-nile hover:underline">Cambiar institución</a>
            </p>
            @error('id_institucion')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Nombre del grupo
            </label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre') }}"
                required
                placeholder="Ej: 3A"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('nombre') border-red-400 @enderror"
            />
            @error('nombre')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        {{-- Materia --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Materia
            </label>
            <input
                type="text"
                name="materia"
                value="{{ old('materia') }}"
                required
                placeholder="Ej: Cálculo Diferencial"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('materia') border-red-400 @enderror"
            />
            @error('materia')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        {{-- Periodo --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Periodo</label>

            @if ($periodos->count() === 0)
                {{-- Sin periodos: bloquear y mostrar mensaje --}}
                <div class="w-full px-4 py-3 bg-orange-50 border border-orange-200 rounded-lg flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-orange-500 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="text-sm font-body font-semibold text-orange-700">No hay periodos configurados</p>
                        <p class="text-xs font-body text-orange-600 mt-0.5">
                            Debes configurar al menos un periodo antes de crear un aula.
                        </p>
                        <a href="{{ route('ca.periodos.index', session('institucion_id')) }}"
                           class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 bg-omg-nile text-white rounded-lg text-xs font-body hover:bg-omg-nile-dark transition-colors">
                            <i class="fa-solid fa-calendar-alt"></i> Configurar periodos
                        </a>
                    </div>
                </div>
                {{-- Input de validación posicionado para mostrar tooltip nativo --}}
                <div class="relative h-0 overflow-visible mt-1">
                    <input type="text" name="_periodo_check" value=""
                           required tabindex="-1"
                           style="width:1px;height:1px;opacity:0;position:absolute;top:0;left:0;pointer-events:none"
                           oninvalid="this.setCustomValidity('Debes configurar periodos antes de crear el aula')"
                           oninput="this.setCustomValidity('')"/>
                </div>
            @elseif ($periodos->count() === 1)
                <input type="hidden" name="periodo" value="{{ $periodos->first()->nombre }}">
                <div class="w-full px-4 py-2.5 bg-omg-chardon border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark flex items-center justify-between">
                    <span>{{ $periodos->first()->nombre }}</span>
                    <i class="fa-solid fa-lock text-omg-kashmir text-xs"></i>
                </div>
                <p class="text-xs font-body text-omg-kashmir mt-1">Único periodo disponible, seleccionado automáticamente.</p>
            @else
                <select name="periodo" required
                        class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('periodo') border-red-400 @enderror">
                    <option value="">Selecciona un periodo</option>
                    @foreach ($periodos as $p)
                        <option value="{{ $p->nombre }}" {{ old('periodo') === $p->nombre ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('periodo')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>



        {{-- Horario por día --}}
        @push('scripts')
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('horarioCreate', () => ({
                filas: [],
                init() {
                    const dias = @json(old('horario_dias', []));
                    const ini  = @json(old('horario_inicio', []));
                    const fin  = @json(old('horario_fin', []));
                    if (dias.length) {
                        this.filas = dias.map((d,i) => ({ dia: d, inicio: ini[i]||'', fin: fin[i]||'' }));
                    }
                },
                agregar() { this.filas.push({ dia: '', inicio: '', fin: '' }); },
                eliminar(i) { this.filas.splice(i, 1); }
            }));
        });
        </script>
        @endpush
        <div x-data="horarioCreate()">
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-body text-omg-dark">
                    Horario <span class="text-omg-kashmir font-normal">(opcional)</span>
                </label>
                <button type="button" @click="agregar()"
                    class="flex items-center gap-1.5 px-3 py-1 bg-omg-chardon hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                    <i class="fa-solid fa-plus"></i> Agregar día
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(fila, i) in filas" :key="i">
                    <div class="flex items-center gap-2 bg-omg-chardon rounded-lg px-3 py-2">
                        {{-- Día --}}
                        <select :name="'horario_dias[' + i + ']'" x-model="fila.dia"
                                class="px-2 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body text-omg-dark focus:outline-none w-24">
                            <option value="">Día</option>
                            <template x-for="op in [{v:'L',l:'Lunes'},{v:'M',l:'Martes'},{v:'X',l:'Miércoles'},{v:'J',l:'Jueves'},{v:'V',l:'Viernes'},{v:'S',l:'Sábado'},{v:'D',l:'Domingo'}]" :key="op.v">
                                <option :value="op.v" :disabled="op.v !== fila.dia && filas.some((f,j) => j !== i && f.dia === op.v)" x-text="op.l"></option>
                            </template>
                        </select>
                        {{-- Hora inicio --}}
                        <div class="flex items-center gap-1 flex-1">
                            <input type="time" :name="'horario_inicio[' + i + ']'" x-model="fila.inicio"
                                   class="flex-1 px-2 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body text-omg-dark focus:outline-none"/>
                            <span class="text-xs text-omg-kashmir">—</span>
                            <input type="time" :name="'horario_fin[' + i + ']'" x-model="fila.fin"
                                   class="flex-1 px-2 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body text-omg-dark focus:outline-none"/>
                        </div>
                        {{-- Eliminar --}}
                        <button type="button" @click="eliminar(i)"
                                class="w-7 h-7 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>

            <p x-show="filas.length === 0" class="text-xs font-body text-omg-kashmir italic mt-1">
                Sin horario definido — presiona "Agregar día" para comenzar
            </p>
            {{-- Input de validación — visible pero sin apariencia, posicionado para que el tooltip aparezca aquí --}}
            <div class="relative h-0 overflow-visible">
                <input type="hidden"
                       name="_horario_check"
                       x-bind:value="filas.length > 0 ? 'ok' : ''">
            </div>
            @error('horario')
                <p class="text-xs text-red-500 mt-2 font-body">{{ $message }}</p>
            @enderror
        </div>


        {{-- No. Alumnos --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Número de alumnos
            </label>
            <input
                type="number"
                name="no_alumnos"
                value="{{ old('no_alumnos') }}"
                required
                min="1"
                placeholder="Ej: 30"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('no_alumnos') border-red-400 @enderror"
            />
            @error('no_alumnos')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        {{-- Botones --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('ca.grupos.index') }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
                <i class="fa-solid fa-ban"></i>
                Cancelar
            </a>
            <button
                type="submit"
                class="flex items-center gap-2 px-4 py-2.5 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold rounded-lg transition-colors text-sm">
                <i class="fa-solid fa-check"></i>
                Guardar
            </button>
        </div>

    </form>
</div>

@endsection