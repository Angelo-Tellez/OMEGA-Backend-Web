{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/reportes/detalle_alumno.blade.php
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
 * Vista Blade — Detalle de asistencias por alumno
 * Modulo: Reportes
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}}
@extends('layouts.app')
@section('title', 'Historial — ' . $alumno->nombre)
@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1 text-sm font-body text-omg-kashmir">
            <a href="{{ route('ca.reportes.index') }}" class="hover:text-omg-nile">Reportes</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <a href="{{ route('ca.reportes.detalle', $grupo->id_grupo) }}" class="hover:text-omg-nile">{{ $grupo->nombre }}</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>{{ $alumno->nombre }} {{ $alumno->ap_pat }}</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">
            {{ $alumno->ap_pat }} {{ $alumno->ap_mat }}, {{ $alumno->nombre }}
        </h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            {{ $grupo->materia }} · {{ $grupo->nombre }} · {{ $grupo->periodo }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('ca.dashboard.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a href="{{ route('ca.reportes.detalle', $grupo->id_grupo) }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Componente AJAX Alpine --}}
<div x-data="{
    sesiones: [],
    resumen: { presentes: 0, ausentes: 0, justificadas: 0, total: 0, porcentaje: 0 },
    cargando: true,
    desde: '',
    hasta: '',
    estado: '',
    errorFecha: false,
    debounce: null,

    async cargar() {
        if (this.desde && this.hasta && this.desde > this.hasta) {
            this.errorFecha = true; return;
        }
        this.errorFecha = false;
        this.cargando = true;
        const params = new URLSearchParams();
        if (this.desde)  params.set('desde',  this.desde);
        if (this.hasta)  params.set('hasta',  this.hasta);
        if (this.estado) params.set('estado', this.estado);

        const res  = await fetch('{{ route('ca.reportes.alumno.json', [$grupo->id_grupo, $alumno->id_usuario]) }}?' + params.toString(), {
            credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        this.sesiones = data.sesiones;
        this.resumen  = {
            presentes:    data.presentes,
            ausentes:     data.ausentes,
            justificadas: data.justificadas,
            total:        data.total,
            porcentaje:   data.porcentaje,
        };
        this.cargando = false;
    },

    onFecha() {
        clearTimeout(this.debounce);
        this.debounce = setTimeout(() => this.cargar(), 400);
    },

    limpiar() {
        this.desde  = '';
        this.hasta  = '';
        this.estado = '';
        this.errorFecha = false;
        this.cargar();
    },

    colorPct(pct) {
        if (pct >= 80) return 'text-green-600';
        if (pct >= 60) return 'text-yellow-500';
        return 'text-red-500';
    },

    etiqueta(estado) {
        if (estado === 1) return 'Presente';
        if (estado === 2) return 'Ausente';
        if (estado === 3) return 'Justificada';
        return 'Sin registro';
    },
    badgeClass(estado) {
        if (estado === 1) return 'bg-green-100 text-green-700';
        if (estado === 2) return 'bg-red-100 text-red-600';
        if (estado === 3) return 'bg-omg-pastel text-omg-nile';
        return 'bg-omg-chardon text-omg-kashmir';
    },
    icono(estado) {
        if (estado === 1) return 'fa-circle-check';
        if (estado === 2) return 'fa-circle-xmark';
        if (estado === 3) return 'fa-file-circle-check';
        return 'fa-minus';
    }
}" x-init="cargar()">

    {{-- Resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 text-center">
            <p class="text-2xl font-heading font-bold" :class="colorPct(resumen.porcentaje)" x-text="resumen.porcentaje + '%'"></p>
            <p class="text-xs font-body text-omg-kashmir mt-1">Asistencia</p>
        </div>
        <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 text-center">
            <p class="text-2xl font-heading font-bold text-omg-kashmir" x-text="resumen.total"></p>
            <p class="text-xs font-body text-omg-kashmir mt-1">Sesiones</p>
        </div>
        <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 text-center">
            <p class="text-2xl font-heading font-bold text-green-600" x-text="resumen.presentes"></p>
            <p class="text-xs font-body text-omg-kashmir mt-1">Asistencias</p>
        </div>
        <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 text-center">
            <p class="text-2xl font-heading font-bold text-red-500" x-text="resumen.ausentes"></p>
            <p class="text-xs font-body text-omg-kashmir mt-1">Faltas</p>
        </div>
        <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 text-center">
            <p class="text-2xl font-heading font-bold text-omg-nile" x-text="resumen.justificadas"></p>
            <p class="text-xs font-body text-omg-kashmir mt-1">Justificaciones</p>
        </div>
    </div>

    {{-- Filtros AJAX --}}
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-4 mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-40">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Desde</label>
                <input type="date" x-model="desde" @change="onFecha()"
                       class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"
                       :class="errorFecha ? 'border-red-400' : ''"/>
            </div>
            <div class="w-40">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Hasta</label>
                <input type="date" x-model="hasta" @change="onFecha()"
                       class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"
                       :class="errorFecha ? 'border-red-400' : ''"/>
            </div>
            <div class="w-44">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Estado</label>
                <select x-model="estado" @change="cargar()"
                        class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <option value="">Todos</option>
                    <option value="1">Solo asistencias</option>
                    <option value="2">Solo faltas</option>
                    <option value="3">Solo justificaciones</option>
                </select>
            </div>
            <button type="button" @click="limpiar()"
                    x-show="desde || hasta || estado"
                    class="flex items-center gap-2 px-4 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                <i class="fa-solid fa-xmark"></i> Limpiar
            </button>
        </div>
        <p x-show="errorFecha" class="mt-2 text-xs text-red-500 font-body">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            La fecha inicial no puede ser posterior a la fecha final.
        </p>
    </div>

    {{-- Loading --}}
    <div x-show="cargando" class="flex justify-center py-12">
        <i class="fa-solid fa-spinner fa-spin text-omg-nile fa-2x"></i>
    </div>

    {{-- Tabla --}}
    <div x-show="!cargando" class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-omg-kashmir-dark bg-omg-chardon">
                    <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">#</th>
                    <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Fecha de la sesión</th>
                    <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Apertura</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Estado</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Hora registro</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-omg-kashmir-dark">
                <template x-if="sesiones.length === 0">
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm font-body text-omg-kashmir">
                            No hay sesiones con los filtros seleccionados
                        </td>
                    </tr>
                </template>
                <template x-for="s in sesiones" :key="s.num">
                    <tr class="hover:bg-omg-chardon transition-colors">
                        <td class="px-5 py-3 text-sm font-body text-omg-kashmir" x-text="s.num"></td>
                        <td class="px-5 py-3 text-sm font-body text-omg-dark" x-text="s.fecha"></td>
                        <td class="px-5 py-3 text-sm font-body text-omg-kashmir" x-text="s.hora"></td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-body px-2 py-1 rounded-full"
                                  :class="badgeClass(s.estado)">
                                <i class="fa-solid text-xs" :class="icono(s.estado)"></i>
                                <span x-text="etiqueta(s.estado)"></span>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center text-xs font-body text-omg-kashmir" x-text="s.hora_registro"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

@endsection
