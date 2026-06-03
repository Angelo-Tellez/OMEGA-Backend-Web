{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/dashboard/partials/riesgo.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
@if ($alumnosEnRiesgo->count() > 0)
<div id="riesgo-resultados">

    {{-- Placeholder cuando no hay institución --}}
    @if (!$filtroInst)
        <div class="px-5 py-8 text-center border-t border-omg-kashmir-dark">
            <i class="fa-solid fa-building-columns text-omg-nile fa-2x mb-3 opacity-40"></i>
            <p class="text-sm font-body text-omg-nile font-semibold">Selecciona una institución para ver los grupos en riesgo</p>
            <p class="text-xs font-body text-omg-kashmir mt-1">Usa el filtro de arriba para comenzar</p>
        </div>
    @else
        {{-- Grupos en acordeón --}}
        @forelse ($riesgoPorGrupo as $grupoId => $items)
            @php
                $grupo    = $items->first()['grupo'];
                $perdidos = $items->where('perdio', true)->count();
                $enRiesgo = $items->where('perdio', false)->count();
            @endphp
            <div class="border-b border-omg-kashmir-dark last:border-b-0"
                 x-data="{ abierto: false }">

                <button @click="abierto = !abierto"
                        class="w-full flex items-center justify-between px-5 py-3 bg-omg-chardon hover:bg-orange-50 transition-colors">
                    <div class="flex items-center gap-3 text-left">
                        <i class="fa-solid fa-chalkboard-user text-omg-nile text-sm"></i>
                        <div>
                            <p class="text-sm font-heading font-semibold text-omg-nile">
                                {{ $grupo->nombre }} — {{ $grupo->materia }}
                            </p>
                            <p class="text-xs font-body text-omg-kashmir">{{ $grupo->periodo }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($perdidos > 0)
                            <span class="bg-red-100 text-red-600 text-xs font-body px-2 py-0.5 rounded-full">{{ $perdidos }} excedido(s)</span>
                        @endif
                        @if ($enRiesgo > 0)
                            <span class="bg-orange-100 text-orange-600 text-xs font-body px-2 py-0.5 rounded-full">{{ $enRiesgo }} en riesgo</span>
                        @endif
                        <i class="fa-solid fa-chevron-down text-omg-kashmir text-xs transition-transform duration-200"
                           :class="abierto ? 'rotate-180' : ''"></i>
                    </div>
                </button>

                <div x-show="abierto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-white border-t border-omg-kashmir-dark">
                                <th class="text-left px-5 py-2 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Alumno</th>
                                <th class="text-center px-5 py-2 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">% Asistencia</th>
                                <th class="text-center px-5 py-2 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Total faltas</th>
                                <th class="text-center px-5 py-2 text-xs font-heading font-semibold text-omg-nile uppercase tracking-wide">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-omg-kashmir-dark">
                            @foreach ($items->sortBy('porcentaje') as $item)
                                <tr class="hover:bg-omg-chardon transition-colors">
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-body font-semibold text-omg-dark">
                                            {{ $item['alumno']->ap_pat }} {{ $item['alumno']->ap_mat }}, {{ $item['alumno']->nombre }}
                                        </p>
                                        <p class="text-xs font-body text-omg-kashmir">{{ $item['alumno']->email }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-sm font-heading font-bold {{ $item['perdio'] ? 'text-red-500' : 'text-orange-500' }}">
                                            {{ $item['porcentaje'] }}%
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-sm font-heading font-bold {{ $item['total_faltas'] >= 3 ? 'text-red-500' : 'text-orange-500' }}">
                                            {{ $item['total_faltas'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php
                                            $totalRubros = isset($item['rubros']) ? count($item['rubros']) : 0;
                                            $idx         = $item['idx_rubro_derecho'] ?? null;
                                            $nombre      = $item['nombre_rubro_derecho'] ?? null;

                                            if ($totalRubros === 0) {
                                                // Sin rubros configurados: fallback original
                                                if ($item['perdio']) {
                                                    $badgeClass = 'bg-red-100 text-red-600';
                                                    $badgeText  = 'Sin derecho a evaluaciones';
                                                } else {
                                                    $badgeClass = 'bg-orange-100 text-orange-600';
                                                    $badgeText  = 'En riesgo';
                                                }
                                            } elseif ($idx === null || $nombre === null) {
                                                // No cumple ningún rubro
                                                $badgeClass = 'bg-red-100 text-red-600';
                                                $badgeText  = 'Sin derecho a evaluaciones';
                                            } elseif ($idx === 0) {
                                                // Cumple el rubro más exigente (1.º, ordenado desc)
                                                $badgeClass = 'bg-green-100 text-green-700';
                                                $badgeText  = 'Derecho: ' . $nombre;
                                            } elseif ($totalRubros >= 3 && $idx === 1) {
                                                // Cumple el segundo rubro (de tres o más)
                                                $badgeClass = 'bg-yellow-100 text-yellow-700';
                                                $badgeText  = 'Derecho: ' . $nombre;
                                            } else {
                                                // Solo tiene derecho al último rubro (el menos exigente)
                                                $badgeClass = 'bg-red-100 text-red-600';
                                                $badgeText  = 'Derecho: ' . $nombre;
                                            }
                                        @endphp
                                        <span class="{{ $badgeClass }} text-xs font-body px-2 py-1 rounded-full whitespace-nowrap">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm font-body text-omg-kashmir">No hay alumnos en riesgo con los filtros seleccionados</p>
            </div>
        @endforelse
    @endif
</div>
@else
<div id="riesgo-resultados">
    <div class="px-5 py-8 text-center border-t border-omg-kashmir-dark">
        <i class="fa-solid fa-building-columns text-omg-nile fa-2x mb-3 opacity-40"></i>
        <p class="text-sm font-body text-omg-nile font-semibold">Selecciona una institución para ver los grupos en riesgo</p>
        <p class="text-xs font-body text-omg-kashmir mt-1">Usa el filtro de arriba para comenzar</p>
    </div>
</div>
@endif
