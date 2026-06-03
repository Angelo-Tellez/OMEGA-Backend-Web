{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/perfil/index.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
@extends('layouts.app')
@section('title', 'Mi Perfil')
@section('content')

{{-- Título --}}
<div class="mb-6">
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">Mi Perfil</h1>
    <p class="text-sm font-body text-omg-kashmir mt-1">
        Administra tu información personal y contraseña
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Datos personales --}}
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-6">
        <h2 class="text-base font-heading font-semibold text-omg-nile mb-5">
            <i class="fa-solid fa-user me-2 text-omg-coral"></i>
            Datos personales
        </h2>

        @if ($errors->hasBag('perfil') || (!$errors->hasBag('contrasenia') && $errors->any()))
            <div class="flex items-start gap-3 bg-white border border-red-200 rounded-lg px-4 py-3 mb-5">
                <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
                <ul class="text-sm text-omg-dark space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('ca.perfil.actualizar') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Nombre</label>
                <input type="text" name="nombre"
                    value="{{ old('nombre', $usuario->nombre) }}"
                    required
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('nombre') border-red-400 @enderror"/>
                @error('nombre')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Apellido paterno</label>
                <input type="text" name="ap_pat"
                    value="{{ old('ap_pat', $usuario->ap_pat) }}"
                    required
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('ap_pat') border-red-400 @enderror"/>
                @error('ap_pat')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Apellido materno</label>
                <input type="text" name="ap_mat"
                    value="{{ old('ap_mat', $usuario->ap_mat) }}"
                    required
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('ap_mat') border-red-400 @enderror"/>
                @error('ap_mat')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Correo electrónico</label>
                <input type="email" name="email"
                    value="{{ old('email', $usuario->email) }}"
                    required
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('email') border-red-400 @enderror"/>
                @error('email')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- Rol deshabilitado --}}
            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Rol</label>
                <input type="text"
                    value="{{ $usuario->rol === 1 ? 'Docente' : 'Alumno' }}"
                    disabled
                    class="w-full px-4 py-2.5 bg-omg-pastel border border-omg-kashmir rounded-lg text-sm font-body text-omg-kashmir cursor-not-allowed"/>
                <p class="text-xs font-body text-omg-kashmir mt-1 italic">El rol no es editable</p>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2.5 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold rounded-lg transition-colors text-sm">
                    <i class="fa-solid fa-check"></i>
                    Actualizar
                </button>
            </div>
        </form>
    </div>

    {{-- Cambiar contraseña --}}
    <div class="bg-white rounded-xl border border-omg-kashmir-dark p-6">
        <h2 class="text-base font-heading font-semibold text-omg-nile mb-5">
            <i class="fa-solid fa-lock me-2 text-omg-coral"></i>
            Cambiar contraseña
        </h2>

        <form method="POST" action="{{ route('ca.perfil.contrasenia') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Contraseña actual</label>
                <input type="password" name="contrasenia_actual" required
                    placeholder="Tu contraseña actual"
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('contrasenia_actual') border-red-400 @enderror"/>
                @error('contrasenia_actual')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Contraseña nueva</label>
                <input type="password" name="contrasenia_nueva" required
                    placeholder="Mínimo 8 caracteres"
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir @error('contrasenia_nueva') border-red-400 @enderror"/>
                @error('contrasenia_nueva')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-body text-omg-dark mb-1">Confirmar contraseña nueva</label>
                <input type="password" name="contrasenia_nueva_confirmation" required
                    placeholder="Repite la contraseña nueva"
                    class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir"/>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2.5 bg-omg-nile hover:bg-omg-nile-dark text-white font-heading font-semibold rounded-lg transition-colors text-sm">
                    <i class="fa-solid fa-lock"></i>
                    Cambiar contraseña
                </button>
            </div>
        </form>
    </div>

</div>

@endsection