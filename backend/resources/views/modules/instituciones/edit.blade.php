{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/modules/instituciones/edit.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}
@extends('layouts.app')
@section('title', 'Editar Institución')
@section('content')

{{-- Título --}}
<div class="mb-6">
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">Editar Institución</h1>
    <p class="text-sm font-body text-omg-kashmir mt-1">
        Modifica la información de {{ $institucion->nombre }}
    </p>
</div>

{{-- Formulario --}}
<div class="bg-white rounded-xl border border-omg-kashmir-dark p-6 max-w-lg">

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

    <form method="POST"
          action="{{ route('ca.instituciones.update', $institucion->id_institucion) }}"
          class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                Nombre de la institución
            </label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre', $institucion->nombre) }}"
                required
                placeholder="Ej: Tecnológico de Toluca"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('nombre') border-red-400 @enderror"
            />
            @error('nombre')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
        </div>

        {{-- Logo URL con preview --}}
        <div>
            <label class="block text-sm font-body text-omg-dark mb-1">
                URL del logotipo
            </label>

            {{-- Preview del logo actual --}}
            <div class="mb-3 flex items-center gap-3">
                <div class="w-16 h-16 rounded-lg border border-omg-kashmir-dark bg-omg-chardon flex items-center justify-center overflow-hidden">
                    @if ($institucion->logo)
                        <img
                            id="logo-preview"
                            src="{{ $institucion->logo }}"
                            alt="Logo actual"
                            class="h-12 w-auto object-contain"
                            onerror="this.style.display='none';document.getElementById('logo-placeholder').style.display='flex'"
                        />
                        <div id="logo-placeholder" class="hidden items-center justify-center w-full h-full">
                            <i class="fa-solid fa-image text-omg-kashmir text-xl"></i>
                        </div>
                    @else
                        <i class="fa-solid fa-image text-omg-kashmir text-xl"></i>
                    @endif
                </div>
                <p class="text-xs font-body text-omg-kashmir">Vista previa del logo actual</p>
            </div>

            <input
                type="text"
                name="logo"
                id="logo-input"
                value="{{ old('logo', $institucion->logo) }}"
                placeholder="https://ejemplo.com/logo.png"
                class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body text-omg-dark focus:outline-none focus:ring-2 focus:ring-omg-kashmir focus:border-transparent @error('logo') border-red-400 @enderror"
            />
            @error('logo')
                <p class="text-xs text-red-500 mt-1 italic font-body">{{ $message }}</p>
            @enderror
            <p class="text-xs font-body text-omg-kashmir mt-1 italic">
                Ingresa la URL de la imagen del logo
            </p>
        </div>

        {{-- Botones --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('ca.instituciones.index') }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon hover:bg-omg-pastel text-omg-nile font-heading font-semibold rounded-lg transition-colors text-sm">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
            <button
                type="submit"
                class="flex items-center gap-2 px-4 py-2.5 bg-omg-coral hover:bg-omg-coral-dark text-white font-heading font-semibold rounded-lg transition-colors text-sm">
                <i class="fa-solid fa-check"></i>
                Actualizar
            </button>
        </div>

    </form>
</div>

{{-- Script para preview en tiempo real --}}
<script>
    document.getElementById('logo-input').addEventListener('input', function () {
        const url = this.value.trim();
        const preview = document.getElementById('logo-preview');
        const placeholder = document.getElementById('logo-placeholder');
        if (url && preview) {
            preview.src = url;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
    });
</script>

@endsection
