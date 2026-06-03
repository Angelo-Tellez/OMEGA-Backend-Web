{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/partials/session-messages.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@foreach (['success' => ['green', 'fa-circle-check'], 'error' => ['red', 'fa-circle-xmark'], 'warning' => ['yellow', 'fa-triangle-exclamation'], 'info' => ['blue', 'fa-circle-info']] as $tipo => $cfg)
    @if (session($tipo))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="mx-6 mt-4 flex items-center gap-3 bg-white border border-{{ $cfg[0] }}-200 rounded-lg px-4 py-3">
            <i class="fa-solid {{ $cfg[1] }} text-{{ $cfg[0] }}-500"></i>
            <p class="text-sm text-omg-dark flex-1">{{ session($tipo) }}</p>
            <button @click="show = false"
                    class="ml-auto text-omg-kashmir hover:text-omg-nile transition-colors"
                    aria-label="Cerrar">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
    @endif
@endforeach
