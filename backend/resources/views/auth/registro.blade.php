{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/auth/registro.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.guest')
@section('title', 'Crear Cuenta')
@section('content')

<div class="bg-omg-chardon rounded-2xl shadow-lg p-8">

    {{-- Logo --}}
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-omg-nile rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-omg-white font-heading font-semibold text-xl">CA</span>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">
            Crear tu Cuenta
        </h1>
        <p class="text-sm font-body text-omg-kashmir mt-1">
            Regístrate para comenzar a controlar asistencias
        </p>
    </div>

    {{-- Errores --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 bg-white border border-red-200 rounded-lg px-4 py-3 mb-5">
            <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
            <ul class="text-sm text-omg-dark space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <form method="POST" action="{{ route('ca.registro.post') }}" class="space-y-4">
        @csrf

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                placeholder="Juan"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('nombre') border-red-400 @enderror"/>
            @error('nombre')
                <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
            @enderror
        </div>

        {{-- Apellido paterno --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Apellido paterno</label>
            <input type="text" name="ap_pat" value="{{ old('ap_pat') }}" required
                placeholder="García"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('ap_pat') border-red-400 @enderror"/>
            @error('ap_pat')
                <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
            @enderror
        </div>

        {{-- Apellido materno --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Apellido materno</label>
            <input type="text" name="ap_mat" value="{{ old('ap_mat') }}" required
                placeholder="López"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('ap_mat') border-red-400 @enderror"/>
            @error('ap_mat')
                <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                placeholder="ejemplo@institucion.com"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('email') border-red-400 @enderror"/>
            @error('email')
                <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
            @enderror
        </div>

        {{-- Contraseña --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Contraseña</label>
            <input type="password" name="contrasenia" required
                placeholder="Mínimo 8 caracteres"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('contrasenia') border-red-400 @enderror"/>
            @error('contrasenia')
                <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirmar contraseña --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">Confirmar contraseña</label>
            <input type="password" name="contrasenia_confirmation" required
                placeholder="Repite tu contraseña"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir"/>
        </div>

        {{-- Botón --}}
        <button type="submit"
            class="w-full bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2 mt-2">
            <i class="fa-solid fa-user-plus"></i>
            Crear cuenta
        </button>

    </form>

    {{-- Login --}}
    <p class="text-center text-sm font-body text-omg-dark mt-6">
        ¿Ya tienes una cuenta?
        <a href="{{ route('ca.login') }}" class="text-omg-nile-light hover:underline font-semibold">
            Iniciar sesión
        </a>
    </p>

</div>

@endsection