{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/layouts/guest.blade.php
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Control de Asistencias')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-omg-white py-10">

    <div class="w-full px-4" style="max-width: 480px;">
        @yield('content')
    </div>

    <footer class="mt-8 w-full text-center pb-4">
        <div class="flex justify-center gap-4 mb-1">
            <a href="#" class="text-xs text-omg-nile-light hover:underline">Términos de uso</a>
            <a href="#" class="text-xs text-omg-nile-light hover:underline">Aviso de privacidad</a>
            <a href="#" class="text-xs text-omg-nile-light hover:underline">Soporte técnico</a>
            <a href="#" class="text-xs text-omg-nile-light hover:underline">Preguntas frecuentes</a>
        </div>
        <p class="text-xs text-omg-dark">© 2026 OMEGA – Control de Asistencias</p>
    </footer>

    @livewireScripts
</body>
</html>