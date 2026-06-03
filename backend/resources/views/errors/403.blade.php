{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/errors/403.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
@extends('layouts.app')
@section('title', 'Acceso restringido')
@section('content')

<div class="flex flex-col items-center justify-center py-20">
    <div class="w-20 h-20 bg-omg-chardon rounded-2xl flex items-center justify-center mb-6">
        <i class="fa-solid fa-crown text-omg-coral fa-2x"></i>
    </div>
    <h1 class="text-2xl font-heading font-semibold text-omg-nile mb-2">Función exclusiva del Plan Mensual</h1>
    <p class="text-sm font-body text-omg-kashmir text-center max-w-md mb-8">
        {{ $exception->getMessage() ?? 'Esta función no está disponible en el Plan Básico. Actualiza tu plan para acceder.' }}
    </p>
    <div class="flex gap-4">
        <a href="{{ url()->previous() }}"
           class="px-5 py-2.5 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-xl text-sm hover:bg-omg-pastel transition-colors">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
        <a href="{{ route('ca.suscripcion.index') }}"
           class="px-5 py-2.5 bg-omg-coral text-white font-heading font-semibold rounded-xl text-sm hover:bg-omg-coral-dark transition-colors">
            <i class="fa-solid fa-crown mr-1"></i> Ver Plan Mensual
        </a>
    </div>
</div>

@endsection
