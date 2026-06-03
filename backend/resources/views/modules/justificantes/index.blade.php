{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/justificantes/index.blade.php
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
 * Vista Blade — Gestion de justificantes de asistencia
 * Modulo: Justificantes
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}}
@extends('layouts.app')
@section('title', 'Justificantes')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">Justificantes</h1>
    <p class="text-sm font-body text-omg-kashmir mt-1">Gestiona las ausencias y justificantes de tus alumnos</p>
</div>

<div x-data="{
    grupos: [],
    cargando: true,
    periodo: '',
    grupo: '',
    desde: '',
    hasta: '',
    errorFecha: false,
    debounce: null,
    csrf: document.querySelector('meta[name=csrf-token]').content,

    async cargar() {
        if (this.desde && this.hasta && this.desde > this.hasta) {
            this.errorFecha = true; return;
        }
        this.errorFecha = false;
        this.cargando = true;
        const params = new URLSearchParams();
        if (this.periodo) params.set('periodo', this.periodo);
        if (this.grupo)   params.set('grupo',   this.grupo);
        if (this.desde)   params.set('desde',   this.desde);
        if (this.hasta)   params.set('hasta',   this.hasta);

        const res  = await fetch('{{ route('ca.justificantes.json') }}?' + params.toString(), {
            credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        this.grupos = await res.json();
        this.cargando = false;
    },

    onFecha() {
        clearTimeout(this.debounce);
        this.debounce = setTimeout(() => this.cargar(), 400);
    },

    limpiar() {
        this.periodo = ''; this.grupo = ''; this.desde = ''; this.hasta = '';
        this.errorFecha = false;
        this.cargar();
    },

    async justificar(alumno) {
        if (alumno.cargando) return;
        alumno.cargando = true;
        const res = await fetch(alumno.url_justificar, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const d = await res.json();
        if (d.ok) alumno.estado = 3;
        alumno.cargando = false;
    },

    async revertir(alumno) {
        if (alumno.cargando) return;
        alumno.cargando = true;
        const res = await fetch(alumno.url_ausente, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const d = await res.json();
        if (d.ok) alumno.estado = 2;
        alumno.cargando = false;
    }
}" x-init="cargar()">

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 mb-6">
        <div class="flex flex-wrap items-end gap-3">

            {{-- Grupo --}}
            <div class="flex-1 min-w-44">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Grupo</label>
                <select x-model="grupo" @change="cargar()"
                        class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <option value="">Todos los grupos</option>
                    @foreach ($todosGrupos as $g)
                        <option value="{{ $g->id_grupo }}">{{ $g->nombre }} — {{ $g->materia }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Periodo --}}
            <div class="flex-1 min-w-36">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Periodo</label>
                <select x-model="periodo" @change="cargar()"
                        class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <option value="">Todos los periodos</option>
                    @foreach ($periodos as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Desde --}}
            <div class="w-36">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Desde</label>
                <input type="date" x-model="desde" @change="onFecha()"
                       class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"
                       :class="errorFecha ? 'border-red-400' : ''"/>
            </div>

            {{-- Hasta --}}
            <div class="w-36">
                <label class="block text-xs font-body text-omg-kashmir mb-1">Hasta</label>
                <input type="date" x-model="hasta" @change="onFecha()"
                       class="w-full px-3 py-2 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"
                       :class="errorFecha ? 'border-red-400' : ''"/>
            </div>

            <button type="button" @click="limpiar()"
                    x-show="periodo || grupo || desde || hasta"
                    class="flex items-center gap-1.5 px-3 py-2 bg-omg-chardon text-omg-nile rounded-lg text-xs font-body hover:bg-omg-pastel transition-colors">
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

    {{-- Grupos --}}
    <div x-show="!cargando">
        <template x-if="grupos.length === 0">
            <div class="bg-white rounded-xl border border-omg-kashmir-dark p-12 text-center">
                <i class="fa-solid fa-file-circle-check text-omg-kashmir fa-2x mb-3"></i>
                <p class="text-sm font-body text-omg-kashmir">No hay ausencias ni justificantes con los filtros seleccionados</p>
            </div>
        </template>

        <template x-for="grupo in grupos" :key="grupo.id">
            <div class="bg-white rounded-xl border border-omg-kashmir-dark mb-4 overflow-hidden"
                 x-data="{ abierto: false }">

                {{-- Header grupo --}}
                <button @click="abierto = !abierto"
                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-omg-chardon transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-chalkboard-user text-omg-nile"></i>
                        <div class="text-left">
                            <p class="text-sm font-heading font-semibold text-omg-nile"
                               x-text="grupo.nombre + ' — ' + grupo.materia"></p>
                            <p class="text-xs font-body text-omg-kashmir" x-text="grupo.periodo"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="bg-red-100 text-red-600 text-xs font-body px-2 py-1 rounded-full"
                              x-text="grupo.sesiones.reduce((t,s) => t + s.alumnos.filter(a => a.estado===2).length, 0) + ' ausente(s)'"
                              x-show="grupo.sesiones.reduce((t,s) => t + s.alumnos.filter(a => a.estado===2).length, 0) > 0">
                        </span>
                        <span class="bg-omg-pastel text-omg-nile text-xs font-body px-2 py-1 rounded-full"
                              x-text="grupo.sesiones.reduce((t,s) => t + s.alumnos.filter(a => a.estado===3).length, 0) + ' justificada(s)'"
                              x-show="grupo.sesiones.reduce((t,s) => t + s.alumnos.filter(a => a.estado===3).length, 0) > 0">
                        </span>
                        <i class="fa-solid fa-chevron-down text-omg-kashmir transition-transform duration-200"
                           :class="abierto ? 'rotate-180' : ''"></i>
                    </div>
                </button>

                {{-- Sesiones --}}
                <div x-show="abierto" x-collapse>
                    <template x-for="sesion in grupo.sesiones" :key="sesion.id">
                        <div class="border-t border-omg-kashmir-dark" x-data="{ sesAbierta: false }">

                            <button @click="sesAbierta = !sesAbierta"
                                    class="w-full flex items-center justify-between px-5 py-3 bg-omg-chardon hover:bg-omg-pastel transition-colors">
                                <div class="flex items-center gap-2">
                                    <i class="fa-regular fa-calendar text-omg-kashmir text-xs"></i>
                                    <p class="text-sm font-body font-semibold text-omg-dark"
                                       x-text="sesion.dia + ' ' + sesion.fecha"></p>
                                    <span class="text-xs font-body text-omg-kashmir"
                                          x-text="'· ' + sesion.hora_apertura + (sesion.hora_cierre ? ' – ' + sesion.hora_cierre : '')"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="bg-red-100 text-red-600 text-xs font-body px-2 py-0.5 rounded-full"
                                          x-show="sesion.ausentes > 0"
                                          x-text="sesion.ausentes + ' ausente(s)'"></span>
                                    <span class="bg-green-100 text-green-600 text-xs font-body px-2 py-0.5 rounded-full"
                                          x-show="sesion.justificadas > 0"
                                          x-text="sesion.justificadas + ' justificada(s)'"></span>
                                    <i class="fa-solid fa-chevron-down text-omg-kashmir text-xs transition-transform duration-200"
                                       :class="sesAbierta ? 'rotate-180' : ''"></i>
                                </div>
                            </button>

                            {{-- Alumnos --}}
                            <div x-show="sesAbierta" x-collapse>
                                <div class="divide-y divide-omg-kashmir-dark">
                                    <template x-for="alumno in sesion.alumnos" :key="alumno.id">
                                        <div class="flex items-center gap-3 px-5 py-3 hover:bg-omg-chardon transition-colors">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-body font-semibold text-omg-dark truncate"
                                                   x-text="alumno.nombre"></p>
                                                <p class="text-xs font-body text-omg-kashmir"
                                                   x-text="alumno.email"></p>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span x-show="alumno.estado === 2"
                                                      class="bg-red-100 text-red-600 text-xs font-body px-2 py-1 rounded-full">Ausente</span>
                                                <span x-show="alumno.estado === 3"
                                                      class="bg-green-100 text-green-600 text-xs font-body px-2 py-1 rounded-full">Justificada</span>

                                                <button x-show="alumno.estado === 2" :disabled="alumno.cargando"
                                                        @click="
                                                            alumno.cargando = true;
                                                            fetch(alumno.url_justificar, {
                                                                method: 'POST',
                                                                credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }
                                                            }).then(r=>r.json()).then(d=>{ if(d.ok) alumno.estado=3; }).finally(()=>alumno.cargando=false);
                                                        "
                                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-nile hover:bg-omg-nile-dark text-white rounded-lg text-xs font-body transition-colors">
                                                    <i class="fa-solid fa-file-circle-check" x-show="!alumno.cargando"></i>
                                                    <i class="fa-solid fa-spinner fa-spin" x-show="alumno.cargando"></i>
                                                    Justificar
                                                </button>
                                                <button x-show="alumno.estado === 3" :disabled="alumno.cargando"
                                                        @click="
                                                            alumno.cargando = true;
                                                            fetch(alumno.url_ausente, {
                                                                method: 'POST',
                                                                credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' }
                                                            }).then(r=>r.json()).then(d=>{ if(d.ok) alumno.estado=2; }).finally(()=>alumno.cargando=false);
                                                        "
                                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-chardon hover:bg-red-500 hover:text-white text-omg-kashmir rounded-lg text-xs font-body transition-colors">
                                                    <i class="fa-solid fa-rotate-left" x-show="!alumno.cargando"></i>
                                                    <i class="fa-solid fa-spinner fa-spin" x-show="alumno.cargando"></i>
                                                    Revertir
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

@endsection
