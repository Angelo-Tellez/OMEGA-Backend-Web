{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/reportes/detalle.blade.php
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
 * Vista Blade — Detalle de reporte por grupo
 * Modulo: Reportes
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}}
@extends('layouts.app')
@section('title', 'Reporte — ' . $grupo->nombre)
@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1 text-sm font-body text-omg-kashmir">
            <a href="{{ route('ca.reportes.index') }}" class="hover:text-omg-nile">Reportes</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>{{ $grupo->nombre }}</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">
            {{ $grupo->nombre }} — {{ $grupo->materia }}
        </h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">{{ $grupo->periodo }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('ca.dashboard.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a href="{{ route('ca.reportes.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Exportar --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('ca.reportes.excel', $grupo->id_grupo) }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-heading font-semibold rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-file-excel"></i> Exportar Excel
    </a>
    <a href="{{ route('ca.reportes.pdf', $grupo->id_grupo) }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-heading font-semibold rounded-lg transition-colors text-sm">
        <i class="fa-solid fa-file-pdf"></i> Exportar PDF
    </a>

</div>

{{-- Sección sesiones con AJAX --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden mb-6"
     x-data="{
         abierto: false,
         sesiones: [],
         cargando: false,
         orden: 'asc',

         async cargar() {
             this.cargando = true;
             const res = await fetch('{{ route('ca.reportes.sesiones.json', $grupo->id_grupo) }}?orden=' + this.orden, {
                 credentials: 'same-origin',
                 headers: { 'X-Requested-With': 'XMLHttpRequest' }
             });
             this.sesiones = await res.json();
             this.cargando = false;
         },

         cambiarOrden(v) {
             this.orden = v;
             this.cargar();
         }
     }"
     x-init="cargar()">

    <button @click="abierto = !abierto"
            class="w-full flex items-center justify-between px-5 py-4 hover:bg-omg-chardon transition-colors">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-calendar-days text-omg-nile"></i>
            <div class="text-left">
                <p class="text-base font-heading font-semibold text-omg-nile">Total de sesiones</p>
                <p class="text-xs font-body text-omg-kashmir" x-text="sesiones.length + ' sesión(es) registradas'"></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{-- Selector orden AJAX --}}
            <select @click.stop @change="cambiarOrden($event.target.value)" x-model="orden"
                    class="px-3 py-1.5 bg-white border border-omg-kashmir rounded-lg text-xs font-body focus:outline-none">
                <option value="asc">Más antigua primero</option>
                <option value="desc">Más reciente primero</option>
            </select>
            <i class="fa-solid fa-chevron-down text-omg-kashmir transition-transform duration-200"
               :class="abierto ? 'rotate-180' : ''"></i>
        </div>
    </button>

    <div x-show="abierto" x-collapse>
        <div x-show="cargando" class="flex justify-center py-8 border-t border-omg-kashmir-dark">
            <i class="fa-solid fa-spinner fa-spin text-omg-nile fa-lg"></i>
        </div>
        <table class="w-full" x-show="!cargando">
            <thead>
                <tr class="border-t border-b border-omg-kashmir-dark bg-omg-chardon">
                    <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">#</th>
                    <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Fecha de la sesión</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Asistencias</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Faltas</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Justificaciones</th>
                    <th class="text-right px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-omg-kashmir-dark">
                <template x-if="sesiones.length === 0">
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm font-body text-omg-kashmir">Sin sesiones registradas</td></tr>
                </template>
                <template x-for="s in sesiones" :key="s.num">
                    <tr class="hover:bg-omg-chardon transition-colors">
                        <td class="px-5 py-3 text-sm font-body text-omg-kashmir" x-text="s.num"></td>
                        <td class="px-5 py-3">
                            <p class="text-sm font-body text-omg-dark" x-text="s.fecha"></p>
                            <p class="text-xs font-body text-omg-kashmir"
                               x-text="s.hora_a + (s.hora_c ? ' — ' + s.hora_c : '')"></p>
                        </td>
                        <td class="px-5 py-3 text-center text-sm font-heading font-semibold text-green-600" x-text="s.presentes"></td>
                        <td class="px-5 py-3 text-center text-sm font-heading font-semibold text-red-500" x-text="s.ausentes"></td>
                        <td class="px-5 py-3 text-center text-sm font-heading font-semibold text-omg-nile" x-text="s.justif"></td>
                        <td class="px-5 py-3 text-right">
                            <a :href="s.url"
                               class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors ml-auto w-fit">
                                <i class="fa-solid fa-ellipsis"></i> Detalles
                            </a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

{{-- Sección alumnos con AJAX --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden"
     x-data="{
         abierto: false,
         alumnos: [],
         cargando: false,
         nombre: '',
         ordenarPor: '',
         dir: 'desc',
         debounce: null,

         async cargar() {
             this.cargando = true;
             const params = new URLSearchParams();
             if (this.nombre)    params.set('nombre',   this.nombre);
             if (this.ordenarPor) { params.set('ordenar', this.ordenarPor); params.set('dir', this.dir); }
             const res = await fetch('{{ route('ca.reportes.alumnos.json', $grupo->id_grupo) }}?' + params.toString(), {
                 credentials: 'same-origin',
                 headers: { 'X-Requested-With': 'XMLHttpRequest' }
             });
             this.alumnos = await res.json();
             this.cargando = false;
         },

         onNombre() {
             clearTimeout(this.debounce);
             this.debounce = setTimeout(() => this.cargar(), 400);
         },

         setOrden(campo) {
             if (this.ordenarPor === campo) {
                 this.dir = this.dir === 'desc' ? 'asc' : 'desc';
             } else {
                 this.ordenarPor = campo;
                 this.dir = 'desc';
             }
             this.cargar();
         },

         limpiar() {
             this.nombre = '';
             this.ordenarPor = '';
             this.dir = 'desc';
             this.cargar();
         },

         colorPct(pct) {
             if (pct >= 80) return 'text-green-600';
             if (pct >= 60) return 'text-yellow-500';
             return 'text-red-500';
         }
     }"
     x-init="cargar()">

    <button @click="abierto = !abierto; if(abierto && alumnos.length === 0) cargar()"
            class="w-full flex items-center justify-between px-5 py-4 hover:bg-omg-chardon transition-colors">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-users text-omg-nile"></i>
            <div class="text-left">
                <p class="text-base font-heading font-semibold text-omg-nile">Detalle por alumno</p>
                <p class="text-xs font-body text-omg-kashmir mt-0.5">Historial sesión a sesión de cada alumno</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-omg-kashmir transition-transform duration-200"
           :class="abierto ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="abierto" x-collapse>

        {{-- Filtros AJAX --}}
        <div class="px-5 py-3 border-t border-omg-kashmir-dark bg-omg-chardon flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Buscar alumno</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-omg-kashmir text-xs"></i>
                    <input type="text" x-model="nombre" @input="onNombre()"
                           placeholder="Nombre o apellido..."
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                </div>
            </div>
            <div>
                <label class="block text-xs font-body text-omg-kashmir mb-1">Ordenar por</label>
                <div class="flex items-center gap-1.5">
                    <button type="button"
                            @click="setOrden('asistencias')"
                            :class="ordenarPor === 'asistencias' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-omg-nile border-omg-kashmir hover:border-green-600'"
                            class="flex items-center gap-1 px-2.5 py-1.5 border rounded-lg text-xs font-body transition-colors">
                        <i class="fa-solid fa-sort-down text-xs" x-show="ordenarPor === 'asistencias' && dir === 'desc'"></i>
                        <i class="fa-solid fa-sort-up text-xs"   x-show="ordenarPor === 'asistencias' && dir === 'asc'"></i>
                        Asistencias
                    </button>
                    <button type="button"
                            @click="setOrden('faltas')"
                            :class="ordenarPor === 'faltas' ? 'bg-red-500 text-white border-red-500' : 'bg-white text-omg-nile border-omg-kashmir hover:border-red-400'"
                            class="flex items-center gap-1 px-2.5 py-1.5 border rounded-lg text-xs font-body transition-colors">
                        <i class="fa-solid fa-sort-down text-xs" x-show="ordenarPor === 'faltas' && dir === 'desc'"></i>
                        <i class="fa-solid fa-sort-up text-xs"   x-show="ordenarPor === 'faltas' && dir === 'asc'"></i>
                        Faltas
                    </button>
                    <button type="button"
                            @click="setOrden('justificaciones')"
                            :class="ordenarPor === 'justificaciones' ? 'bg-omg-nile text-white border-omg-nile' : 'bg-white text-omg-nile border-omg-kashmir hover:border-omg-nile'"
                            class="flex items-center gap-1 px-2.5 py-1.5 border rounded-lg text-xs font-body transition-colors">
                        <i class="fa-solid fa-sort-down text-xs" x-show="ordenarPor === 'justificaciones' && dir === 'desc'"></i>
                        <i class="fa-solid fa-sort-up text-xs"   x-show="ordenarPor === 'justificaciones' && dir === 'asc'"></i>
                        Justificaciones
                    </button>
                    <button type="button" @click="limpiar()"
                            x-show="nombre || ordenarPor"
                            class="px-2.5 py-1.5 bg-omg-chardon text-omg-kashmir border border-omg-kashmir rounded-lg text-xs font-body hover:bg-omg-pastel transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="cargando" class="flex justify-center py-8 border-t border-omg-kashmir-dark">
            <i class="fa-solid fa-spinner fa-spin text-omg-nile fa-lg"></i>
        </div>

        {{-- Tabla --}}
        @php
            use App\Models\RubroEvaluacion;
            $rubros = RubroEvaluacion::where('id_institucion', $grupo->id_institucion)
                ->orderBy('porcentaje_minimo', 'desc')->get();
        @endphp
        <table class="w-full" x-show="!cargando">
            <thead>
                <tr class="border-t border-b border-omg-kashmir-dark bg-omg-chardon">
                    <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Alumno</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Asistencias</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Faltas</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Justificaciones</th>
                    <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">% Asistencia</th>
                    @foreach ($rubros as $rubro)
                        <th class="text-center px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">
                            {{ $rubro->nombre }}
                            <span class="block text-omg-kashmir font-normal normal-case">mín. {{ $rubro->porcentaje_minimo }}%</span>
                        </th>
                    @endforeach
                    <th class="text-right px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-omg-kashmir-dark">
                <template x-if="alumnos.length === 0">
                    <tr>
                        <td colspan="{{ 6 + $rubros->count() }}" class="px-5 py-8 text-center text-sm font-body text-omg-kashmir">
                            Sin alumnos encontrados
                        </td>
                    </tr>
                </template>
                <template x-for="al in alumnos" :key="al.id">
                    <tr class="hover:bg-omg-chardon transition-colors">
                        <td class="px-5 py-3">
                            <p class="text-sm font-body font-semibold text-omg-dark" x-text="al.nombre"></p>
                            <p class="text-xs font-body text-omg-kashmir" x-text="al.email"></p>
                        </td>
                        <td class="px-5 py-3 text-center text-sm font-heading font-semibold text-green-600" x-text="al.p"></td>
                        <td class="px-5 py-3 text-center text-sm font-heading font-semibold text-red-500" x-text="al.a"></td>
                        <td class="px-5 py-3 text-center text-sm font-heading font-semibold text-omg-nile" x-text="al.j"></td>
                        <td class="px-5 py-3 text-center">
                            <span class="text-sm font-heading font-bold" :class="colorPct(al.pct)" x-text="al.pct + '%'"></span>
                        </td>
                        @foreach ($rubros as $rubro)
                            <td class="px-5 py-3 text-center">
                                <template x-if="al.pct >= {{ $rubro->porcentaje_minimo }}">
                                    <i class="fa-solid fa-circle-check text-green-500 fa-lg"></i>
                                </template>
                                <template x-if="al.pct < {{ $rubro->porcentaje_minimo }}">
                                    <i class="fa-solid fa-circle-xmark text-red-500 fa-lg"></i>
                                </template>
                            </td>
                        @endforeach
                        <td class="px-5 py-3 text-right">
                            <a :href="al.url"
                               class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors ml-auto w-fit">
                                <i class="fa-solid fa-chart-line"></i> Ver historial
                            </a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

@endsection