{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/layouts/app.blade.php
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
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <title>@yield('title', 'Control de Asistencias')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-omg-white min-h-screen flex">

    @include('partials.sidebar')

    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ml-64"
         id="main-content">
        @include('partials.session-messages')
        <main class="flex-1 p-6">
            @yield('content')
        </main>
        @include('partials.footer')
    </div>

    @stack('scripts')
    @livewireScripts
</body>
</html>