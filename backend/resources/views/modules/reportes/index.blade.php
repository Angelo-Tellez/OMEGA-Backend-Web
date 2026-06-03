{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/reportes/index.blade.php
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
 * Vista Blade — Lista de reportes del docente
 * Modulo: Reportes
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}
@extends('layouts.app')
@section('title', 'Reportes')
@section('content')

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">Reportes</h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">Visualiza el resumen de asistencias por grupo</p>
</div>

{{-- Componente Alpine con AJAX --}}
<div x-data="{
    reportes: [],
    cargando: true,
    busqueda: '{{ $busqueda }}',
    periodo: '{{ $periodo }}',
    minPct: '{{ $minPct }}',
    maxPct: '{{ $maxPct }}',
    debounce: null,

    async cargar() {
        this.cargando = true;
        const params = new URLSearchParams();
        if (this.busqueda) params.set('grupo', this.busqueda);
        if (this.periodo)  params.set('periodo',  this.periodo);
        if (this.minPct)   params.set('min_pct',  this.minPct);
        if (this.maxPct)   params.set('max_pct',  this.maxPct);

        const res  = await fetch('{{ route('ca.reportes.json') }}?' + params.toString(), {
            credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        this.reportes = await res.json();
        this.cargando = false;
    },

    onBusqueda() {
        clearTimeout(this.debounce);
        this.debounce = setTimeout(() => this.cargar(), 400);
    },

    limpiar() {
        this.busqueda = '';
        this.periodo  = '';
        this.minPct   = '';
        this.maxPct   = '';
        this.cargar();
    },

    colorPct(pct) {
        if (pct >= 80) return 'text-green-600';
        if (pct >= 60) return 'text-yellow-500';
        return 'text-red-500';
    },
    barColor(pct) {
        if (pct >= 80) return 'bg-green-500';
        if (pct >= 60) return 'bg-yellow-400';
        return 'bg-red-500';
    },
    descripcion(pct) {
        if (pct >= 80) return 'Los alumnos asisten con regularidad. El grupo mantiene un buen ritmo.';
        if (pct >= 60) return 'Asistencia moderada. Algunos alumnos podrían estar en riesgo.';
        return 'Asistencia baja. Se recomienda revisar el estado del grupo.';
    },
    icono(pct) {
        if (pct >= 80) return 'fa-circle-check';
        if (pct >= 60) return 'fa-triangle-exclamation';
        return 'fa-circle-xmark';
    }
}" x-init="cargar()">

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-body text-omg-kashmir mb-1">Grupo</label>
                <select x-model="busqueda" @change="cargar()"
                        class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <option value="">Todos los grupos</option>
                    @foreach ($grupos as $g)
                        <option value="{{ $g->nombre }}">{{ $g->nombre }} — {{ $g->materia }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-body text-omg-kashmir mb-1">Periodo</label>
                <select x-model="periodo" @change="cargar()"
                        class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <option value="">Todos los periodos</option>
                    @foreach ($periodos as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-body text-omg-kashmir mb-1">% Asistencia mínimo</label>
                <input type="number" x-model="minPct" @change="cargar()" min="0" max="100"
                       placeholder="Ej: 60"
                       class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
            </div>
            <div>
                <label class="block text-xs font-body text-omg-kashmir mb-1">% Asistencia máximo</label>
                <input type="number" x-model="maxPct" @change="cargar()" min="0" max="100"
                       placeholder="Ej: 80"
                       class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
            </div>
        </div>
        <div class="flex items-center justify-between mt-3">
            <p class="text-xs font-body text-omg-kashmir" x-text="cargando ? 'Cargando...' : reportes.length + ' grupo(s) encontrado(s)'"></p>
            <button type="button" @click="limpiar()"
                    x-show="busqueda || periodo || minPct || maxPct"
                    class="px-3 py-1.5 bg-omg-chardon text-omg-kashmir hover:text-omg-nile rounded-lg text-xs font-body transition-colors">
                <i class="fa-solid fa-xmark mr-1"></i> Limpiar filtros
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="cargando" class="flex justify-center py-12">
        <i class="fa-solid fa-spinner fa-spin text-omg-nile fa-2x"></i>
    </div>

    {{-- Tarjetas --}}
    <div x-show="!cargando">
        <template x-if="reportes.length === 0">
            <div class="bg-white rounded-xl border border-omg-kashmir-dark p-12 text-center">
                <i class="fa-solid fa-chart-bar text-omg-kashmir fa-2x mb-3"></i>
                <p class="text-sm font-body text-omg-kashmir">No hay grupos que coincidan con los filtros</p>
            </div>
        </template>

        <template x-for="r in reportes" :key="r.id">
            <div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 mb-4">
                <div class="mb-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-heading font-semibold text-omg-nile"
                                x-text="r.nombre + ' — ' + r.materia"></h3>
                            <p class="text-xs font-body text-omg-kashmir mt-0.5"
                               x-text="r.periodo + ' · ' + r.total_sesiones + ' sesión(es)'"></p>
                            {{-- Porcentaje debajo del periodo --}}
                            <p class="text-sm font-heading font-bold mt-1 text-omg-nile"
                               x-text="'Porcentaje de asistencias totales: ' + r.porcentaje + '%'"></p>
                        </div>
                        <a :href="r.url_detalle"
                           class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors flex-shrink-0 ml-4">
                            <i class="fa-solid fa-ellipsis"></i> Detalles
                        </a>
                    </div>
                </div>

                {{-- Barra --}}
                <div class="w-full bg-omg-chardon rounded-full h-2 mb-2">
                    <div class="h-2 rounded-full transition-all" :class="barColor(r.porcentaje)" :style="'width:' + r.porcentaje + '%'"></div>
                </div>

                {{-- Descripción --}}
                <p class="text-xs font-body mb-3" :class="colorPct(r.porcentaje)">
                    <i class="fa-solid mr-1" :class="icono(r.porcentaje)"></i>
                    <span x-text="descripcion(r.porcentaje)"></span>
                </p>

                {{-- Contadores --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-lg font-heading font-semibold text-green-600" x-text="r.presentes"></p>
                        <p class="text-xs font-body text-omg-kashmir">Asistencias</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <p class="text-lg font-heading font-semibold text-red-500" x-text="r.ausentes"></p>
                        <p class="text-xs font-body text-omg-kashmir">Faltas</p>
                    </div>
                    <div class="bg-omg-chardon rounded-lg p-3 text-center">
                        <p class="text-lg font-heading font-semibold text-omg-nile" x-text="r.justif"></p>
                        <p class="text-xs font-body text-omg-kashmir">Justificaciones</p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@endsection
