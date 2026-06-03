{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/sesiones/index.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
@extends('layouts.app')
@section('title', 'Sesiones — ' . $grupo->nombre)
@section('content')

{{-- Título --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ca.grupos.index') }}"
               class="text-sm font-body text-omg-nile-light hover:underline">
                Mis Aulas
            </a>
            <i class="fa-solid fa-chevron-right text-omg-kashmir text-xs"></i>
            <span class="text-sm font-body text-omg-dark">{{ $grupo->nombre }}</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">
            Sesiones — {{ $grupo->nombre }}
        </h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            {{ $grupo->materia }} · {{ $grupo->periodo }}
        </p>
    </div>

    {{-- Botones --}}
    @php
        $sesionActiva = $sesiones->firstWhere('est_sesion', 1);
    @endphp
    <div class="flex items-center gap-2">
        <a href="{{ route('ca.grupos.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i> Volver
        </a>

    @if (!$sesionActiva)
        <form method="POST" action="{{ route('ca.grupos.sesiones.abrir', $grupo->id_grupo) }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold px-4 py-2.5 rounded-lg transition-colors text-sm">
                <i class="fa-solid fa-play"></i>
                Abrir sesión
            </button>
        </form>
    @else
        <div class="flex items-center gap-3" x-data="sesionTimer()">
            {{-- Clave activa --}}
            <div class="bg-omg-nile text-white px-4 py-2.5 rounded-lg text-center">
                <p class="text-xs font-body opacity-75">Clave activa</p>
                <p class="text-xl font-heading font-semibold tracking-widest">{{ $sesionActiva->clave }}</p>
                <p class="text-xs font-body opacity-75 mt-1">
                    <i class="fa-regular fa-clock"></i>
                    <span x-text="tiempo">00:00</span>
                </p>
            </div>
            {{-- Cerrar sesión con confirmación RF-52, RF-53 --}}
            <button @click="confirmar = true"
                class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white font-heading font-semibold px-4 py-2.5 rounded-lg transition-colors text-sm">
                <i class="fa-solid fa-stop"></i>
                Cerrar sesión
            </button>

            {{-- Dialog de confirmación RF-52 --}}
            <div x-show="confirmar" x-transition
                 class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-heading font-semibold text-omg-nile">¿Cerrar sesión?</p>
                            <p class="text-xs font-body text-omg-kashmir">Los alumnos ya no podrán registrarse</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="confirmar = false"
                            class="flex-1 py-2 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                            Cancelar
                        </button>
                        <form method="POST" action="{{ route('ca.sesiones.cerrar', $sesionActiva->id_sesion) }}" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="w-full py-2 bg-red-500 hover:bg-red-600 text-white font-heading font-semibold rounded-lg text-sm transition-colors">
                                Cerrar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
        function sesionTimer() {
            return {
                confirmar: false,
                tiempo: '00:00',
                init() {
                    const apertura = new Date('{{ $sesionActiva->hora_apertura->toIso8601String() }}');
                    setInterval(() => {
                        const diff = Math.floor((new Date() - apertura) / 1000);
                        const m = String(Math.floor(diff / 60)).padStart(2, '0');
                        const s = String(diff % 60).padStart(2, '0');
                        this.tiempo = m + ':' + s;
                    }, 1000);
                }
            };
        }
        </script>
        @endpush
    @endif
    </div>{{-- /flex botones --}}
</div>

{{-- Sesión activa banner --}}
@if ($sesionActiva)
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-6">
        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
        <p class="text-sm font-body text-omg-dark">
            Sesión activa desde <strong>{{ $sesionActiva->hora_apertura->format('H:i') }}</strong> —
            Los alumnos pueden registrar asistencia con la clave
            <strong class="text-omg-nile tracking-widest">{{ $sesionActiva->clave }}</strong>
        </p>
    </div>
@endif

{{-- Tabla de sesiones --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-omg-kashmir-dark bg-omg-chardon">
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Fecha</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Apertura</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Cierre</th>
                <th class="text-left px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Estado</th>
                <th class="text-right px-5 py-3 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-omg-kashmir-dark">
            @forelse ($sesiones as $sesion)
                <tr class="hover:bg-omg-chardon transition-colors">
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-dark">
                            {{ $sesion->fec_sesion->format('d/m/Y') }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-dark">
                            {{ $sesion->hora_apertura->format('H:i') }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-body text-omg-kashmir">
                            {{ $sesion->hora_cierre ? $sesion->hora_cierre->format('H:i') : '—' }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        @if ($sesion->est_sesion === 1)
                            <span class="bg-green-100 text-green-700 text-xs font-body px-2 py-1 rounded-full">
                                Activa
                            </span>
                        @else
                            <span class="bg-omg-pastel text-omg-kashmir text-xs font-body px-2 py-1 rounded-full">
                                Cerrada
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end">
                            <a href="{{ route('ca.sesiones.asistencias', $sesion->id_sesion) }}"
                               class="flex items-center gap-1.5 px-3 py-1.5 bg-omg-pastel hover:bg-omg-nile hover:text-white text-omg-nile rounded-lg text-xs font-body transition-colors">
                                <i class="fa-solid fa-ellipsis"></i>
                                Detalles
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center">
                        <i class="fa-solid fa-calendar-day text-omg-kashmir fa-2x mb-3"></i>
                        <p class="text-sm font-body text-omg-kashmir">No se encontraron registros</p>
                        <p class="text-xs font-body text-omg-kashmir mt-1">
                            Abre una sesión para comenzar a registrar asistencias
                        </p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection