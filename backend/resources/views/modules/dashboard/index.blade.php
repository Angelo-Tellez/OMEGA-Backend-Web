{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/dashboard/index.blade.php
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
 * Vista Blade — Dashboard principal del docente
 * Modulo: Dashboard
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}}
@extends('layouts.app')
@section('title', 'Inicio')
@section('content')

{{-- Encabezado --}}
<div class="mb-6">
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">
        Bienvenido, {{ auth()->user()->nombre }} 👋
    </h1>
    <p class="text-sm font-body text-omg-kashmir mt-1">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>
</div>

{{-- Tarjetas resumen --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-omg-chardon rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-chalkboard-user text-omg-nile fa-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-heading font-semibold text-omg-nile">{{ $aulasActivas }}</p>
            <p class="text-xs font-body text-omg-kashmir">Aulas activas</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-omg-chardon rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-calendar-day text-omg-nile fa-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-heading font-semibold text-omg-nile">{{ $sesionesHoy->count() }}</p>
            <p class="text-xs font-body text-omg-kashmir">Sesiones hoy</p>
        </div>
    </div>
    <a href="#alumnos-riesgo"
       class="bg-white rounded-xl border {{ $countRiesgoFiltrado > 0 ? 'border-orange-300' : 'border-omg-kashmir-dark' }} p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
        <div class="w-12 h-12 {{ $countRiesgoFiltrado > 0 ? 'bg-orange-50' : 'bg-omg-chardon' }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-triangle-exclamation {{ $countRiesgoFiltrado > 0 ? 'text-orange-500' : 'text-omg-coral' }} fa-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-heading font-semibold {{ $countRiesgoFiltrado > 0 ? 'text-orange-500' : 'text-omg-nile' }}">
                {{ $countRiesgoFiltrado }}
            </p>
            <p class="text-xs font-body text-omg-kashmir">En riesgo</p>
        </div>
    </a>
    <a href="{{ route('ca.justificantes.index') }}"
       class="bg-white rounded-xl border border-omg-kashmir-dark p-5 flex items-center gap-4 hover:shadow-sm transition-shadow">
        <div class="w-12 h-12 bg-omg-chardon rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-file-circle-check text-omg-nile fa-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-heading font-semibold text-omg-nile">{{ $justificantesPend }}</p>
            <p class="text-xs font-body text-omg-kashmir">Justificantes</p>
        </div>
    </a>
</div>

{{-- Sesiones de hoy --}}
@if ($sesionesHoy->count() > 0)
<div class="bg-white rounded-xl border border-omg-kashmir-dark p-5 mb-6">
    <h2 class="text-base font-heading font-semibold text-omg-nile mb-4">
        <i class="fa-solid fa-circle text-green-400 text-xs mr-1 animate-pulse"></i>
        Sesiones de Hoy
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($sesionesHoy as $item)
            @php $s = $item['sesion']; @endphp
            <div class="border border-omg-kashmir-dark rounded-lg p-4 {{ $s->est_sesion === 1 ? 'border-l-4 border-l-green-400' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-heading font-semibold text-omg-nile">
                        {{ $s->grupo->nombre }} — {{ $s->grupo->materia }}
                    </p>
                    @if ($s->est_sesion === 1)
                        <span class="bg-green-100 text-green-600 text-xs font-body px-2 py-0.5 rounded-full">Activa</span>
                    @else
                        <span class="bg-omg-chardon text-omg-kashmir text-xs font-body px-2 py-0.5 rounded-full">Cerrada</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-xs font-body text-omg-kashmir">
                    <span><i class="fa-regular fa-clock mr-1"></i>{{ $s->hora_apertura->format('H:i') }}</span>
                    <span><i class="fa-solid fa-users mr-1"></i>{{ $item['presentes'] }}/{{ $item['total'] }}</span>
                </div>
                @if ($s->est_sesion === 1)
                    <div class="mt-2 bg-omg-chardon rounded px-3 py-1.5 text-center">
                        <p class="text-xs font-body text-omg-kashmir">Clave activa</p>
                        <p class="text-lg font-heading font-bold text-omg-nile tracking-widest">{{ $s->clave }}</p>
                    </div>
                @endif
                <a href="{{ route('ca.grupos.sesiones', $s->grupo) }}"
                   class="mt-3 flex items-center justify-center gap-1.5 w-full px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                    <i class="fa-solid fa-arrow-right"></i> Ver sesión
                </a>
            </div>
        @endforeach
    </div>
</div>
@endif

@if ($alumnosEnRiesgo->count() > 0)
<div id="alumnos-riesgo" class="bg-white rounded-xl border border-orange-200 overflow-hidden mb-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 px-5 py-4 bg-orange-50 border-b border-orange-200">
        <i class="fa-solid fa-triangle-exclamation text-orange-500"></i>
        <h2 class="text-base font-heading font-semibold text-orange-700">
            Alumnos en Riesgo ({{ $countRiesgoFiltrado }})
        </h2>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('ca.dashboard.index') }}#alumnos-riesgo"
          class="flex items-center gap-3 flex-wrap px-5 py-3 bg-orange-50 border-b border-orange-200">

        <select name="inst" onchange="this.form.submit()"
                class="px-3 py-1.5 bg-white border border-orange-200 rounded-lg text-xs font-body text-omg-dark focus:outline-none">
            <option value="">Todas las instituciones</option>
            @foreach ($instSelect as $instItem)
                <option value="{{ $instItem['id'] }}" {{ $filtroInst == $instItem['id'] ? 'selected' : '' }}>
                    {{ $instItem['nombre'] }}
                </option>
            @endforeach
        </select>

        <select name="grupo" onchange="this.form.submit()"
                {{ !$filtroInst ? 'disabled' : '' }}
                class="px-3 py-1.5 bg-white border border-orange-200 rounded-lg text-xs font-body text-omg-dark focus:outline-none {{ !$filtroInst ? 'opacity-40' : '' }}">
            <option value="">Todos los grupos</option>
            @foreach ($gruposSelect as $grupoItem)
                <option value="{{ $grupoItem['id'] }}" {{ $filtroGrupo == $grupoItem['id'] ? 'selected' : '' }}>
                    {{ $grupoItem['nombre'] }}
                </option>
            @endforeach
        </select>

        <select name="estado" onchange="this.form.submit()"
                {{ !$filtroInst ? 'disabled' : '' }}
                class="px-3 py-1.5 bg-white border border-orange-200 rounded-lg text-xs font-body text-omg-dark focus:outline-none {{ !$filtroInst ? 'opacity-40' : '' }}">
            <option value="">Todos los estados</option>
            <option value="riesgo"   {{ $filtroEstado === 'riesgo'   ? 'selected' : '' }}>🟢 Dentro del margen de riesgo</option>
            <option value="excedido" {{ $filtroEstado === 'excedido' ? 'selected' : '' }}>🟡 Perdió primera evaluación / 🔴 Perdió segunda</option>
        </select>

        @if ($filtroInst || $filtroGrupo || $filtroEstado)
            <a href="{{ route('ca.dashboard.index') }}#alumnos-riesgo"
               class="px-3 py-1.5 bg-white border border-orange-200 text-orange-600 rounded-lg text-xs font-body hover:bg-orange-100 transition-colors">
                <i class="fa-solid fa-xmark mr-1"></i> Limpiar
            </a>
        @endif
    </form>

    {{-- Leyenda de colores (solo si hay institución seleccionada) --}}
    @if (!empty($filtroInst))
    <div class="px-5 py-3 bg-white border-b border-orange-100 space-y-1.5">
        <div class="flex items-start gap-2">
            <span class="mt-0.5 inline-block w-2.5 h-2.5 rounded-full bg-green-400 flex-shrink-0"></span>
            <span class="text-xs font-body text-omg-dark">Dentro del margen de riesgo (menos del 5% sobre Curso Primera oportunidad)</span>
        </div>
        <div class="flex items-start gap-2">
            <span class="mt-0.5 inline-block w-2.5 h-2.5 rounded-full bg-yellow-400 flex-shrink-0"></span>
            <span class="text-xs font-body text-omg-dark">Perdió el derecho a la primera evaluación</span>
        </div>
        <div class="flex items-start gap-2">
            <span class="mt-0.5 inline-block w-2.5 h-2.5 rounded-full bg-red-400 flex-shrink-0"></span>
            <span class="text-xs font-body text-omg-dark">Perdió el derecho a la segunda evaluación</span>
        </div>
    </div>
    @endif

    {{-- Resultados (se actualiza por fetch) --}}
    @include('modules.dashboard.partials.riesgo')

</div>
@endif


@endsection
