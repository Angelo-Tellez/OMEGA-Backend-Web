{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/grupos/edit.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.app')
@section('title', 'Editar Aula')
@section('content')

{{-- Título --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Editar Aula</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            Modifica la información de {{ $grupo->nombre }} — {{ $grupo->materia }}
        </p>
    </div>
    <a href="{{ route('ca.grupos.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-arrow-left text-xs"></i> Volver
    </a>
</div>

{{-- Formulario --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark p-6 max-w-lg">

    @if ($errors->any())
        <div class="flex items-start gap-3 bg-white border border-red-200 rounded-lg px-4 py-3 mb-6">
            <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
            <ul class="text-sm text-omg-dark space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('ca.grupos.update', $grupo->id_grupo) }}"
          class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Institución --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Institución
            </label>
            <select
                name="id_institucion"
                required
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent">
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id_institucion }}"
                        {{ $grupo->id_institucion == $institucion->id_institucion ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Nombre del grupo
            </label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre', $grupo->nombre) }}"
                required
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
                value="{{ old('materia', $grupo->materia) }}"
                required
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('materia') border-red-400 @enderror"
            />
            @error('materia')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        {{-- Periodo --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Periodo</label>
            @php
                $periodos = \App\Models\Periodo::where('id_institucion', $grupo->id_institucion)
                    ->where('activo', true)->orderByDesc('created_at')->get();
                $periodoActual = old('periodo', $grupo->periodo);
                // Si el periodo actual no está en la lista, agregarlo como opción
                $enLista = $periodos->contains('nombre', $periodoActual);
            @endphp
            @if ($periodos->count() === 0)
                <input type="text" name="periodo" value="{{ $periodoActual }}" required
                       class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('periodo') border-red-400 @enderror"/>
                <p class="text-xs font-body text-omg-kashmir mt-1">
                    <a href="{{ route('ca.periodos.index', $grupo->id_institucion) }}" class="text-omg-nile hover:underline">
                        <i class="fa-solid fa-plus mr-1"></i>Configura periodos
                    </a> para seleccionarlos aquí.
                </p>
            @else
                <select name="periodo" required
                        class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('periodo') border-red-400 @enderror">
                    <option value="">Selecciona un periodo</option>
                    @if (!$enLista && $periodoActual)
                        <option value="{{ $periodoActual }}" selected>{{ $periodoActual }} (actual)</option>
                    @endif
                    @foreach ($periodos as $p)
                        <option value="{{ $p->nombre }}" {{ $periodoActual === $p->nombre ? 'selected' : '' }}>
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
            Alpine.data('horarioEdit', () => ({
                filas: [],
                existentes: @json($grupo->horario ?? []),
                init() {
                    const dias = @json(old('horario_dias'));
                    const ini  = @json(old('horario_inicio', []));
                    const fin  = @json(old('horario_fin', []));
                    if (dias && dias.length) {
                        this.filas = dias.map((d,i) => ({ dia: d, inicio: ini[i]||'', fin: fin[i]||'' }));
                    } else if (this.existentes && this.existentes.length) {
                        this.filas = this.existentes.map(e => ({ dia: e.dia, inicio: e.hora_inicio, fin: e.hora_fin }));
                    }
                },
                agregar() { this.filas.push({ dia: '', inicio: '', fin: '' }); },
                eliminar(i) { this.filas.splice(i, 1); }
            }));
        });
        </script>
        @endpush
        <div x-data="horarioEdit()">
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
                        <select :name="'horario_dias[' + i + ']'" x-model="fila.dia"
                                class="px-2 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body text-omg-dark focus:outline-none w-24">
                            <option value="">Día</option>
                            <template x-for="op in [{v:'L',l:'Lunes'},{v:'M',l:'Martes'},{v:'X',l:'Miércoles'},{v:'J',l:'Jueves'},{v:'V',l:'Viernes'},{v:'S',l:'Sábado'},{v:'D',l:'Domingo'}]" :key="op.v">
                                <option :value="op.v" :disabled="op.v !== fila.dia && filas.some((f,j) => j !== i && f.dia === op.v)" x-text="op.l"></option>
                            </template>
                        </select>
                        <div class="flex items-center gap-1 flex-1">
                            <input type="time" :name="'horario_inicio[' + i + ']'" x-model="fila.inicio"
                                   class="flex-1 px-2 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body text-omg-dark focus:outline-none"/>
                            <span class="text-xs text-omg-kashmir">—</span>
                            <input type="time" :name="'horario_fin[' + i + ']'" x-model="fila.fin"
                                   class="flex-1 px-2 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body text-omg-dark focus:outline-none"/>
                        </div>
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
                value="{{ old('no_alumnos', $grupo->no_alumnos) }}"
                required
                min="1"
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
                Actualizar
            </button>
        </div>

    </form>
</div>

@endsection