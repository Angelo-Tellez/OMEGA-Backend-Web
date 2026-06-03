{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/landing.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA — Control de Asistencias</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-white text-gray-800">

{{-- Navbar --}}
<nav class="fixed top-0 w-full bg-white/90 backdrop-blur border-b border-gray-100 z-50">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-omg-nile rounded-lg flex items-center justify-center">
                <span class="text-white text-xs font-heading font-bold">CA</span>
            </div>
            <span class="font-heading font-semibold text-omg-nile">OMEGA Asistencias</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('ca.login') }}" class="text-sm font-body text-omg-kashmir hover:text-omg-nile">Iniciar sesión</a>
            <a href="{{ route('ca.registro') }}"
               class="px-4 py-2 bg-omg-coral text-white text-sm font-heading font-semibold rounded-lg hover:bg-omg-coral-dark transition-colors">
                Comenzar gratis
            </a>
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="pt-32 pb-20 px-6 bg-gradient-to-br from-omg-chardon to-white">
    <div class="max-w-4xl mx-auto text-center">
        <span class="inline-block bg-omg-pastel text-omg-nile text-xs font-body px-3 py-1 rounded-full mb-6">
            Sistema de Control de Asistencias
        </span>
        <h1 class="text-4xl lg:text-5xl font-heading font-bold text-omg-nile mb-6 leading-tight">
            Registra asistencias en<br>
            <span class="text-omg-coral">menos de 5 segundos</span>
        </h1>
        <p class="text-lg font-body text-omg-kashmir max-w-2xl mx-auto mb-10">
            El docente dicta una clave temporal. Los alumnos la ingresan desde su celular.
            Sin QR, sin aglomeraciones, sin fraude.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('ca.registro') }}"
               class="px-8 py-3.5 bg-omg-coral text-white font-heading font-semibold rounded-xl hover:bg-omg-coral-dark transition-colors text-lg">
                Crear cuenta gratis
            </a>
            <a href="#planes"
               class="px-8 py-3.5 bg-white border border-omg-kashmir text-omg-nile font-heading font-semibold rounded-xl hover:bg-omg-chardon transition-colors text-lg">
                Ver planes
            </a>
        </div>
    </div>
</section>

{{-- Características --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-heading font-bold text-omg-nile text-center mb-12">¿Cómo funciona?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ([
                ['icon'=>'fa-key','title'=>'1. Docente genera clave','desc'=>'Al inicio de clase el docente abre la sesión y obtiene una clave única de 6 caracteres.'],
                ['icon'=>'fa-mobile-screen','title'=>'2. Alumnos registran','desc'=>'Los alumnos ingresan la clave desde la app en sus celulares. El registro toma menos de 5 segundos.'],
                ['icon'=>'fa-chart-bar','title'=>'3. Reportes automáticos','desc'=>'El sistema calcula porcentajes, detecta alumnos en riesgo y genera reportes en Excel y PDF.'],
            ] as $f)
                <div class="text-center p-6 bg-omg-chardon rounded-2xl">
                    <div class="w-14 h-14 bg-omg-nile rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid {{ $f['icon'] }} text-white fa-lg"></i>
                    </div>
                    <h3 class="font-heading font-semibold text-omg-nile mb-2">{{ $f['title'] }}</h3>
                    <p class="text-sm font-body text-omg-kashmir">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Planes RF-56 --}}
<section id="planes" class="py-20 px-6 bg-omg-chardon">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-heading font-bold text-omg-nile text-center mb-4">Planes</h2>
        <p class="text-center font-body text-omg-kashmir mb-12">Empieza gratis, escala cuando lo necesites</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Plan Básico --}}
            <div class="bg-white rounded-2xl border border-omg-kashmir-dark p-8">
                <p class="text-sm font-body text-omg-kashmir mb-1">Plan</p>
                <h3 class="text-2xl font-heading font-bold text-omg-nile mb-2">Básico</h3>
                <p class="text-4xl font-heading font-bold text-omg-nile mb-6">Gratis</p>
                <ul class="space-y-3 mb-8">
                    @foreach (['1 aula activa','Hasta 15 alumnos','Historial de 1 semana','Registro con clave temporal','App móvil para alumnos'] as $f)
                        <li class="flex items-center gap-2 text-sm font-body text-omg-dark">
                            <i class="fa-solid fa-check text-green-500 flex-shrink-0"></i>{{ $f }}
                        </li>
                    @endforeach
                    @foreach (['Reportes Excel/PDF','Gestión de justificantes','Historial completo'] as $f)
                        <li class="flex items-center gap-2 text-sm font-body text-omg-kashmir">
                            <i class="fa-solid fa-xmark text-red-400 flex-shrink-0"></i>{{ $f }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('ca.registro') }}"
                   class="block text-center py-3 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-xl hover:bg-omg-pastel transition-colors">
                    Comenzar gratis
                </a>
            </div>

            {{-- Plan Mensual --}}
            <div class="bg-omg-nile rounded-2xl p-8 relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-omg-coral text-white text-xs font-body px-3 py-1 rounded-full">
                    Más popular
                </span>
                <p class="text-sm font-body text-blue-200 mb-1">Plan</p>
                <h3 class="text-2xl font-heading font-bold text-white mb-2">Mensual</h3>
                <p class="text-4xl font-heading font-bold text-white mb-1">$149 <span class="text-lg font-body text-blue-200">MXN/mes</span></p>
                <p class="text-xs font-body text-blue-200 mb-6">por institución contratada</p>
                <ul class="space-y-3 mb-8">
                    @foreach (['Aulas ilimitadas','Hasta 50 alumnos por aula','Historial completo por periodo','Registro con clave temporal','App móvil para alumnos','Reportes Excel y PDF','Gestión de justificantes','Múltiples instituciones','Soporte prioritario'] as $f)
                        <li class="flex items-center gap-2 text-sm font-body text-white">
                            <i class="fa-solid fa-check text-green-400 flex-shrink-0"></i>{{ $f }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('ca.registro') }}"
                   class="block text-center py-3 bg-omg-coral text-white font-heading font-semibold rounded-xl hover:bg-omg-coral-dark transition-colors">
                    Comenzar ahora
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-omg-nile text-white py-12 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm font-body text-blue-200">© 2026 OMEGA Solutions — Sistema de Control de Asistencias</p>
            <div class="flex items-center gap-6 text-sm font-body">
                <a href="#" class="text-blue-200 hover:text-white">Términos de uso</a>
                <a href="#" class="text-blue-200 hover:text-white">Aviso de privacidad</a>
                <a href="#" class="text-blue-200 hover:text-white">Soporte técnico</a>
                <a href="#" class="text-blue-200 hover:text-white">Preguntas frecuentes</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
