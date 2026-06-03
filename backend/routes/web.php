<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GrupoWebController;
use App\Http\Controllers\Web\GrupoAlumnoWebController;
use App\Http\Controllers\Web\InstitucionWebController;
use App\Http\Controllers\Web\JustificanteWebController;
use App\Http\Controllers\Web\RubroWebController;
use App\Http\Controllers\Web\PeriodoWebController;
use App\Http\Controllers\Web\PerfilWebController;
use App\Http\Controllers\Web\ReporteWebController;
use App\Http\Controllers\Web\SesionWebController;
use App\Http\Controllers\Web\SuscripcionWebController;

use Illuminate\Support\Facades\Route;
/*
 * Rutas Web — Sistema de Control de Asistencias
 * Prefijo: /p/ca
 */

// Ruta raíz
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/inicio', function () {
    return redirect()->route('landing');
});

// Rutas públicas
Route::prefix('p/ca')->group(function () {

    // Autenticación
    Route::get('login',    [AuthWebController::class, 'showLogin'])->name('ca.login');
    Route::post('login',   [AuthWebController::class, 'login'])->name('ca.login.post');
    Route::get('registro', [AuthWebController::class, 'showRegistro'])->name('ca.registro');
    Route::post('registro',[AuthWebController::class, 'registro'])->name('ca.registro.post');

    // RF-55 — Recuperación de contraseña
    Route::get('recuperar-contrasena',         [PasswordResetController::class, 'showForgotForm'])->name('ca.password.request');
    Route::post('recuperar-contrasena',        [PasswordResetController::class, 'sendResetLink'])->name('ca.password.email');
    Route::get('reset-contrasena/{token}',     [PasswordResetController::class, 'showResetForm'])->name('ca.password.reset');
    Route::post('reset-contrasena',            [PasswordResetController::class, 'reset'])->name('ca.password.update');

    // Rutas protegidas
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthWebController::class, 'logout'])->name('ca.logout');

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('ca.dashboard.index');
        Route::get('dashboard/riesgo', [DashboardController::class, 'riesgoPartial'])->name('ca.dashboard.riesgo');

        // Perfil
        Route::get('perfil',                        [PerfilWebController::class, 'index'])->name('ca.perfil.index');
        Route::put('perfil',                        [PerfilWebController::class, 'actualizar'])->name('ca.perfil.actualizar');
        Route::put('perfil/contrasenia',            [PerfilWebController::class, 'cambiarContrasenia'])->name('ca.perfil.contrasenia');

        // Instituciones
        Route::get('instituciones',                        [InstitucionWebController::class, 'index'])->name('ca.instituciones.index');
        Route::get('instituciones/crear',                  [InstitucionWebController::class, 'create'])->middleware('plan.basico:institucion')->name('ca.instituciones.create');
        Route::post('instituciones',                       [InstitucionWebController::class, 'store'])->middleware('plan.basico:institucion')->name('ca.instituciones.store');
        Route::get('instituciones/{institucion}/editar',   [InstitucionWebController::class, 'edit'])->name('ca.instituciones.edit');
        Route::put('instituciones/{institucion}',          [InstitucionWebController::class, 'update'])->name('ca.instituciones.update');
        Route::delete('instituciones/{institucion}',       [InstitucionWebController::class, 'destroy'])->name('ca.instituciones.destroy');
        Route::get('instituciones/{id}/ir',            [InstitucionWebController::class, 'seleccionarYRedirigir'])->name('ca.instituciones.ir');
        Route::post('instituciones/{id}/seleccionar',      [InstitucionWebController::class, 'seleccionar'])->name('ca.instituciones.seleccionar');
        // Rubros de evaluacion por institucion (RF-04, RF-05)
        Route::get('instituciones/{id}/rubros',            [RubroWebController::class, 'index'])->name('ca.rubros.index');
        Route::post('instituciones/{id}/rubros',           [RubroWebController::class, 'store'])->name('ca.rubros.store');
        Route::get('instituciones/{id}/periodos',          [PeriodoWebController::class, 'index'])->name('ca.periodos.index');
        Route::post('instituciones/{id}/periodos',         [PeriodoWebController::class, 'store'])->name('ca.periodos.store');
        Route::delete('instituciones/{id}/periodos/{periodo}', [PeriodoWebController::class, 'destroy'])->name('ca.periodos.destroy');
        Route::patch('instituciones/{id}/periodos/{periodo}',  [PeriodoWebController::class, 'update'])->name('ca.periodos.update');
        Route::put('instituciones/{id}/rubros/{rubro}',   [RubroWebController::class, 'update'])->name('ca.rubros.update');
        Route::delete('instituciones/{id}/rubros/{rubro}',[RubroWebController::class, 'destroy'])->name('ca.rubros.destroy');

        // Grupos
        Route::get('grupos',                           [GrupoWebController::class, 'index'])->name('ca.grupos.index');
        Route::get('grupos/crear',                     [GrupoWebController::class, 'create'])->middleware('plan.basico:grupo')->name('ca.grupos.create');
        Route::post('grupos',                          [GrupoWebController::class, 'store'])->middleware('plan.basico:grupo')->name('ca.grupos.store');
        Route::get('grupos/{grupo}/editar',            [GrupoWebController::class, 'edit'])->name('ca.grupos.edit');
        Route::put('grupos/{grupo}',                   [GrupoWebController::class, 'update'])->name('ca.grupos.update');
        Route::delete('grupos/{grupo}',                [GrupoWebController::class, 'destroy'])->name('ca.grupos.destroy');
        Route::post('grupos/{grupo}/codigo-inv',       [GrupoWebController::class, 'generarCodigo'])->name('ca.grupos.codigo-inv');
        Route::get('grupos/{grupo}/alumnos',           [GrupoAlumnoWebController::class, 'index'])->name('ca.grupos.alumnos');
        Route::post('grupos/{grupo}/cerrar-periodo',    [GrupoWebController::class, 'cerrarPeriodo'])->name('ca.grupos.cerrar-periodo');
        Route::delete('grupos/{grupo}/alumnos/{grupoAlumno}', [GrupoAlumnoWebController::class, 'destroy'])->name('ca.grupos.alumnos.destroy');

        // Sesiones
        Route::get('grupos/{grupo}/sesiones',          [SesionWebController::class, 'index'])->name('ca.grupos.sesiones');
        Route::post('grupos/{grupo}/sesiones/abrir',   [SesionWebController::class, 'abrir'])->name('ca.grupos.sesiones.abrir');
        Route::post('sesiones/{sesion}/cerrar',        [SesionWebController::class, 'cerrar'])->name('ca.sesiones.cerrar');
        Route::get('sesiones/{sesion}/asistencias',    [SesionWebController::class, 'asistencias'])->name('ca.sesiones.asistencias');

        // Justificantes
        Route::get('justificantes',                              [JustificanteWebController::class, 'index'])->name('ca.justificantes.index');
        Route::get('justificantes-json',                         [JustificanteWebController::class, 'indexJson'])->name('ca.justificantes.json');
        Route::post('justificantes/{asistencia}/justificar',     [JustificanteWebController::class, 'justificar'])->name('ca.justificantes.justificar');
        Route::post('justificantes/{asistencia}/marcar-ausente', [JustificanteWebController::class, 'marcarAusente'])->name('ca.justificantes.ausente');

        // Reportes
        Route::get('reportes',                [ReporteWebController::class, 'index'])->middleware('plan.basico:reportes')->name('ca.reportes.index');
        Route::get('reportes-json',           [ReporteWebController::class, 'indexJson'])->middleware('plan.basico:reportes')->name('ca.reportes.json');
        Route::get('reportes/{idGrupo}/alumnos-json',  [ReporteWebController::class, 'alumnosJson'])->name('ca.reportes.alumnos.json');
        Route::get('reportes/{idGrupo}/sesiones-json', [ReporteWebController::class, 'sesionesJson'])->name('ca.reportes.sesiones.json');
        Route::get('reportes/{idGrupo}',      [ReporteWebController::class, 'detalle'])->name('ca.reportes.detalle');
        Route::get('reportes/{idGrupo}/excel',  [ReporteWebController::class, 'exportarExcel'])->name('ca.reportes.excel');
        Route::get('reportes/{idGrupo}/pdf',    [ReporteWebController::class, 'exportarPdf'])->name('ca.reportes.pdf');
        Route::get('reportes/{idGrupo}/alumno/{idAlumno}',      [ReporteWebController::class, 'detalleAlumno'])->name('ca.reportes.alumno');
        Route::get('reportes/{idGrupo}/alumno/{idAlumno}/json', [ReporteWebController::class, 'detalleAlumnoJson'])->name('ca.reportes.alumno.json');


        // Suscripción
        Route::get('suscripcion',              [SuscripcionWebController::class, 'index'])->name('ca.suscripcion.index');
        Route::post('suscripcion/crear-orden', [SuscripcionWebController::class, 'crearOrden'])->name('ca.suscripcion.crear-orden');
        Route::get('suscripcion/capturar',     [SuscripcionWebController::class, 'capturarPago'])->name('ca.suscripcion.capturar');
        Route::get('suscripcion/cancelar',     [SuscripcionWebController::class, 'cancelarPago'])->name('ca.suscripcion.cancelar');

    });
});