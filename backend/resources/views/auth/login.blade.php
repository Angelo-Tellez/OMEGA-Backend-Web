{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/auth/login.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
@extends('layouts.guest')

@section('content')
<div class="bg-omg-chardon rounded-2xl shadow-lg p-8">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-omg-nile rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-omg-white font-heading font-semibold text-xl">CA</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">
            Inicia sesión en Control de Asistencias
        </h1>
    </div>

    {{-- Errores --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 bg-white border border-red-200 rounded-lg px-4 py-3 mb-6">
            <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
            <ul class="text-sm text-omg-dark space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <form method="POST" action="{{ route('ca.login.post') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Correo electrónico
            </label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                placeholder="ejemplo@institucion.com"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('email') border-red-400 @enderror"
            />
            @error('email')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Contraseña
            </label>
            <input
                type="password"
                name="contrasenia"
                required
                placeholder="Contraseña"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('contrasenia') border-red-400 @enderror"
            />
            @error('contrasenia')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        <div class="text-right">
            <a href="{{ route('ca.password.request') }}" class="text-xs text-omg-nile-light hover:underline font-body">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        <button
            type="submit"
            class="w-full bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
            <i class="fa-solid fa-right-to-bracket"></i>
            Iniciar sesión
        </button>

    </form>

    <p class="text-center text-sm font-body text-omg-dark mt-6">
        ¿No estás registrado?
        <a href="{{ route('ca.registro') }}" class="text-omg-nile-light hover:underline font-semibold">
            Crear cuenta
        </a>
    </p>

</div>
@endsection