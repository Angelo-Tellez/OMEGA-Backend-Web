{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/auth/forgot-password.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.guest')
@section('title', 'Recuperar contraseña')
@section('content')

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-omg-chardon rounded-2xl mb-4">
            <i class="fa-solid fa-lock-open text-omg-nile fa-xl"></i>
        </div>
        <h1 class="text-2xl font-heading font-semibold text-omg-nile">Recuperar contraseña</h1>
        <p class="text-sm font-body text-omg-kashmir mt-2">
            Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña
        </p>
    </div>

    @if (session('status'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-lg px-4 py-3 mb-6">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            <p class="text-sm font-body text-green-700">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-6">
            <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
            <ul class="text-sm font-body text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-omg-kashmir-dark p-8 shadow-sm">
        <form method="POST" action="{{ route('ca.password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-body text-omg-dark mb-1.5">
                    Correo electrónico
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="docente@ejemplo.com"
                       class="w-full px-4 py-3 bg-white border border-omg-kashmir rounded-xl text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-nile focus:border-transparent"/>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold rounded-xl transition-colors text-sm">
                Enviar enlace de recuperación
            </button>
        </form>
    </div>

    <p class="text-center text-sm font-body text-omg-kashmir mt-6">
        <a href="{{ route('ca.login') }}" class="text-omg-nile hover:underline">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver al inicio de sesión
        </a>
    </p>
</div>

@endsection
