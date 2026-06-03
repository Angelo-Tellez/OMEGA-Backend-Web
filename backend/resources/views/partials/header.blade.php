{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/partials/header.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
<header class="bg-white border-b border-omg-kashmir-dark px-6 py-4 flex items-center justify-between">
    <div>
        @hasSection('breadcrumb')
            @yield('breadcrumb')
        @else
            <p class="text-omg-dark text-sm font-heading font-semibold">
                {{ $title ?? 'Dashboard' }}
            </p>
        @endif
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-omg-kashmir">
            <i class="fa-solid fa-crown text-omg-coral me-1"></i>
            Plan {{ auth()->user()->suscripcion?->plan === 2 ? 'Mensual' : 'Básico' }}
        </span>
    </div>
</header>