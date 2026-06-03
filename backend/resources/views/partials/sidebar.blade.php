{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/partials/sidebar.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
<aside x-data="{ colapsado: false }"
       :class="colapsado ? 'w-16' : 'w-64'"
       class="fixed left-0 top-0 h-full bg-omg-nile flex flex-col z-50 transition-all duration-300">

    {{-- Logo + toggle --}}
    <div class="flex items-center gap-3 px-4 py-5 border-b border-omg-nile-light"
         :class="colapsado ? 'justify-center' : 'justify-between'">
        <div class="flex items-center gap-3" x-show="!colapsado">
            <div class="w-9 h-9 bg-omg-coral rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="text-white font-heading font-semibold text-sm">CA</span>
            </div>
            <div>
                <p class="text-omg-white font-heading font-semibold text-sm leading-tight">Control de</p>
                <p class="text-omg-white font-heading font-semibold text-sm leading-tight">Asistencias</p>
            </div>
        </div>
        <div x-show="colapsado" class="w-9 h-9 bg-omg-coral rounded-lg flex items-center justify-center">
            <span class="text-white font-heading font-semibold text-sm">CA</span>
        </div>
        <button @click="
                    colapsado = !colapsado;
                    const main = document.getElementById('main-content');
                    if (main) main.style.marginLeft = colapsado ? '4rem' : '16rem';
                "
                class="text-omg-kashmir hover:text-white transition-colors flex-shrink-0"
                :title="colapsado ? 'Expandir' : 'Colapsar'">
            <i class="fa-solid transition-transform duration-300"
               :class="colapsado ? 'fa-angles-right' : 'fa-angles-left'"></i>
        </button>
    </div>

    {{-- Institución activa --}}
    @auth
    <div x-data="{
            instNombre: '{{ session('institucion_nombre') }}',
            instId: '{{ session('institucion_id') }}',
            init() {
                window.addEventListener('inst-seleccionada', (e) => {
                    this.instNombre = e.detail.nombre;
                    this.instId     = e.detail.id;
                });
            }
         }">
        {{-- Expandido --}}
        <div :class="instId ? 'bg-omg-nile-dark' : 'bg-orange-900'" class="px-4 py-3" x-show="!colapsado">
            <p class="text-omg-kashmir text-xs">Institución activa</p>
            <template x-if="instId">
                <p class="text-omg-white text-sm font-semibold truncate" x-text="instNombre"></p>
            </template>
            <template x-if="!instId">
                <a href="{{ route('ca.instituciones.index') }}"
                   class="flex items-center gap-1.5 text-orange-300 text-xs font-semibold hover:text-white transition-colors">
                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                    Selecciona una institución
                </a>
            </template>
        </div>
        {{-- Colapsado --}}
        <div :class="instId ? 'bg-omg-nile-dark' : 'bg-orange-900'" class="flex justify-center py-2" x-show="colapsado">
            <i class="fa-solid fa-building text-omg-kashmir text-sm" :title="instNombre || 'Sin institución'"></i>
        </div>
    </div>
    @endauth

    {{-- Navegación --}}
    <nav class="flex-1 px-2 py-4 overflow-y-auto">
        <ul class="space-y-1">

            <li>
                <a href="{{ route('ca.dashboard.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                   {{ request()->routeIs('ca.dashboard.*') ? 'bg-omg-coral text-white' : 'text-omg-kashmir hover:bg-omg-nile-dark hover:text-omg-white' }}"
                   :title="colapsado ? 'Inicio' : ''">
                    <i class="fa-solid fa-house-chimney w-4 flex-shrink-0"></i>
                    <span x-show="!colapsado" class="truncate">Inicio</span>
                </a>
            </li>

            <li>
                <a href="{{ route('ca.instituciones.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                   {{ request()->routeIs('ca.instituciones.*') ? 'bg-omg-coral text-white' : 'text-omg-kashmir hover:bg-omg-nile-dark hover:text-omg-white' }}"
                   :title="colapsado ? 'Mis Instituciones' : ''">
                    <i class="fa-solid fa-building-columns w-4 flex-shrink-0"></i>
                    <span x-show="!colapsado" class="truncate">Mis Instituciones</span>
                </a>
            </li>

            <li>
                <a href="{{ route('ca.grupos.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                   {{ request()->routeIs('ca.grupos.*') ? 'bg-omg-coral text-white' : 'text-omg-kashmir hover:bg-omg-nile-dark hover:text-omg-white' }}"
                   :title="colapsado ? 'Mis Aulas' : ''">
                    <i class="fa-solid fa-chalkboard-user w-4 flex-shrink-0"></i>
                    <span x-show="!colapsado" class="truncate">Mis Aulas</span>
                </a>
            </li>

            <li>
                <a href="{{ route('ca.justificantes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                   {{ request()->routeIs('ca.justificantes.*') ? 'bg-omg-coral text-white' : 'text-omg-kashmir hover:bg-omg-nile-dark hover:text-omg-white' }}"
                   :title="colapsado ? 'Justificantes' : ''">
                    <i class="fa-solid fa-file-circle-check w-4 flex-shrink-0"></i>
                    <span x-show="!colapsado" class="truncate">Justificantes</span>
                </a>
            </li>

            <li>
                @php
                    $suscDoc  = app(\App\Services\SuscripcionService::class)->obtener(auth()->user());
                    $esPremium = $suscDoc['plan'] === 2 && in_array($suscDoc['est_suscripcion'], [1,3]);
                @endphp
                <a href="{{ route('ca.reportes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                   {{ request()->routeIs('ca.reportes.*') ? 'bg-omg-coral text-white' : 'text-omg-kashmir hover:bg-omg-nile-dark hover:text-omg-white' }}"
                   :title="colapsado ? 'Reportes' : ''">
                    <i class="fa-solid fa-chart-bar w-4 flex-shrink-0"></i>
                    <span x-show="!colapsado" class="truncate">Reportes</span>
                    @if (!$esPremium)
                        <i class="fa-solid fa-lock text-xs ml-auto opacity-60" x-show="!colapsado"></i>
                    @endif
                </a>
            </li>

            <li>
                <a href="{{ route('ca.suscripcion.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                   {{ request()->routeIs('ca.suscripcion.*') ? 'bg-omg-coral text-white' : 'text-omg-kashmir hover:bg-omg-nile-dark hover:text-omg-white' }}"
                   :title="colapsado ? 'Mi Suscripción' : ''">
                    <i class="fa-solid fa-crown w-4 flex-shrink-0"></i>
                    <span x-show="!colapsado" class="truncate">Mi Suscripción</span>
                </a>
            </li>

        </ul>
    </nav>

    {{-- Usuario, plan y logout --}}
    @auth
    <div class="px-2 py-4 border-t border-omg-nile-light">

        {{-- Info usuario --}}
        <a href="{{ route('ca.perfil.index') }}"
           class="flex items-center gap-3 mb-2 px-3 py-2 rounded-lg hover:bg-omg-nile-dark transition-colors"
           :title="colapsado ? '{{ auth()->user()->nombre }} {{ auth()->user()->ap_pat }}' : ''">
            <div class="w-8 h-8 bg-omg-coral rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-xs font-semibold">
                    {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}{{ strtoupper(substr(auth()->user()->ap_pat, 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0" x-show="!colapsado">
                <p class="text-omg-white text-xs font-semibold truncate">
                    {{ auth()->user()->nombre }} {{ auth()->user()->ap_pat }}
                </p>
                <p class="text-omg-kashmir text-xs truncate">{{ auth()->user()->email }}</p>
            </div>
        </a>

        {{-- Plan activo --}}
        <div class="flex items-center gap-2 px-3 py-1.5 mb-2 rounded-lg bg-omg-nile-dark"
             x-show="!colapsado">
            <i class="fa-solid fa-crown text-omg-coral text-xs flex-shrink-0"></i>
            <span class="text-xs font-body text-omg-kashmir">
                Plan {{ auth()->user()->suscripcion?->plan === 2 ? 'Mensual' : 'Básico' }}
            </span>
        </div>
        {{-- Plan colapsado --}}
        <div class="flex justify-center mb-2" x-show="colapsado">
            <i class="fa-solid fa-crown text-omg-coral text-sm"
               title="Plan {{ auth()->user()->suscripcion?->plan === 2 ? 'Mensual' : 'Básico' }}"></i>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('ca.logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-omg-kashmir hover:bg-omg-nile-dark hover:text-red-400 transition-colors"
                :title="colapsado ? 'Cerrar sesión' : ''">
                <i class="fa-solid fa-right-from-bracket flex-shrink-0"></i>
                <span x-show="!colapsado">Cerrar sesión</span>
            </button>
        </form>
    </div>
    @endauth

</aside>
