<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : database/seeders/SistemaSeeder.php
// Created on : 27/04/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 27/04/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
//   [002] 03/06/2026 - Angelo Armando Tellez Enriquez - Agregar periodos y corregir plan
// ============================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * SistemaSeeder — Datos de prueba completos para OMEGA Control de Asistencias
 *
 * Usuarios:
 *   Docente 1: cmendoza@omega.com   / Omega2026  → TecToluca (3 grupos, alumnos)
 *   Docente 2: lgutierrez@omega.com / Omega2026  → UAEM     (3 grupos, alumnos)
 *
 * Alumnos (10 en total, contraseña: Omega2026):
 *   sramirez, dtorres, vcastro, aflores, cherrera (TecToluca)
 *   lvargas, imorales, sruiz, fsalinas, mromero  (UAEM)
 *
 * Datos generados:
 *   - 2 periodos activos (uno por institución)
 *   - 6 grupos con horario
 *   - 8 sesiones cerradas por grupo con asistencias variadas
 *   - Alumnos con distintos niveles de asistencia (100%, 80%, 60%, en riesgo)
 *   - 1 sesión activa en el grupo 216000 con clave PRUEBA
 *   - Suscripciones Plan Mensual activas para ambos docentes
 */
class SistemaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('asistencias')->truncate();
        DB::table('sesiones')->truncate();
        DB::table('grupo_alumnos')->truncate();
        DB::table('grupos')->truncate();
        DB::table('periodos')->truncate();
        DB::table('rubros_evaluacion')->truncate();
        DB::table('instituciones')->truncate();
        DB::table('suscripciones')->truncate();
        DB::table('personal_access_tokens')->truncate();
        DB::table('usuarios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $pass = Hash::make('Omega2026');
        $now  = Carbon::now();

        // ─── DOCENTES ────────────────────────────────────────────────────────
        $d1 = DB::table('usuarios')->insertGetId([
            'nombre'      => 'Carlos',
            'ap_pat'      => 'Mendoza',
            'ap_mat'      => 'Rios',
            'email'       => 'cmendoza@omega.com',
            'contrasenia' => $pass,
            'rol'         => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $d2 = DB::table('usuarios')->insertGetId([
            'nombre'      => 'Laura',
            'ap_pat'      => 'Gutierrez',
            'ap_mat'      => 'Vega',
            'email'       => 'lgutierrez@omega.com',
            'contrasenia' => $pass,
            'rol'         => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // ─── ALUMNOS ─────────────────────────────────────────────────────────
        $alumnosData = [
            ['Sofia',     'Ramirez',  'Luna',    'sramirez@omega.com'],
            ['Diego',     'Torres',   'Mora',    'dtorres@omega.com'],
            ['Valeria',   'Castro',   'Perez',   'vcastro@omega.com'],
            ['Andres',    'Flores',   'Reyes',   'aflores@omega.com'],
            ['Camila',    'Herrera',  'Diaz',    'cherrera@omega.com'],
            ['Luis',      'Vargas',   'Ortiz',   'lvargas@omega.com'],
            ['Isabella',  'Morales',  'Jimenez', 'imorales@omega.com'],
            ['Sebastian', 'Ruiz',     'Mendez',  'sruiz@omega.com'],
            ['Fernanda',  'Salinas',  'Cruz',    'fsalinas@omega.com'],
            ['Miguel',    'Romero',   'Aguilar', 'mromero@omega.com'],
        ];

        $als = [];
        foreach ($alumnosData as $a) {
            $als[] = DB::table('usuarios')->insertGetId([
                'nombre'      => $a[0],
                'ap_pat'      => $a[1],
                'ap_mat'      => $a[2],
                'email'       => $a[3],
                'contrasenia' => $pass,
                'rol'         => 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // ─── INSTITUCIONES ───────────────────────────────────────────────────
        // Logo almacenado como texto descriptivo (sin URLs externas)
        $i1 = DB::table('instituciones')->insertGetId([
            'id_docente' => $d1,
            'nombre'     => 'Tecnologico de Toluca',
            'logo'       => 'tec_toluca',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $i2 = DB::table('instituciones')->insertGetId([
            'id_docente' => $d2,
            'nombre'     => 'Universidad Autonoma del Estado de Mexico',
            'logo'       => 'uaem',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ─── RUBROS DE EVALUACIÓN ─────────────────────────────────────────────
        foreach ([$i1, $i2] as $inst) {
            DB::table('rubros_evaluacion')->insert([
                [
                    'id_institucion'    => $inst,
                    'nombre'            => 'Ordinario',
                    'porcentaje_minimo' => 80.00,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ],
                [
                    'id_institucion'    => $inst,
                    'nombre'            => 'Extraordinario',
                    'porcentaje_minimo' => 60.00,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ],
            ]);
        }

        // ─── PERIODOS ACADÉMICOS ──────────────────────────────────────────────
        // Cada institución tiene su propio periodo activo
        $p1 = DB::table('periodos')->insertGetId([
            'id_institucion' => $i1,
            'nombre'         => 'Enero Junio 2026',
            'activo'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $p2 = DB::table('periodos')->insertGetId([
            'id_institucion' => $i2,
            'nombre'         => 'Enero Junio 2026',
            'activo'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        // Periodos anteriores (inactivos) para mostrar historial
        DB::table('periodos')->insert([
            [
                'id_institucion' => $i1,
                'nombre'         => 'Agosto Diciembre 2025',
                'activo'         => false,
                'created_at'     => Carbon::now()->subMonths(8),
                'updated_at'     => Carbon::now()->subMonths(8),
            ],
            [
                'id_institucion' => $i2,
                'nombre'         => 'Agosto Diciembre 2025',
                'activo'         => false,
                'created_at'     => Carbon::now()->subMonths(8),
                'updated_at'     => Carbon::now()->subMonths(8),
            ],
        ]);

        // ─── GRUPOS ───────────────────────────────────────────────────────────
        $horarioLMV = json_encode([
            ['dia' => 'L', 'hora_inicio' => '08:00', 'hora_fin' => '10:00'],
            ['dia' => 'M', 'hora_inicio' => '08:00', 'hora_fin' => '10:00'],
            ['dia' => 'V', 'hora_inicio' => '08:00', 'hora_fin' => '10:00'],
        ]);
        $horarioMAJ = json_encode([
            ['dia' => 'M', 'hora_inicio' => '10:00', 'hora_fin' => '12:00'],
            ['dia' => 'J', 'hora_inicio' => '10:00', 'hora_fin' => '12:00'],
        ]);

        $grupos = [
            [$i1, $d1, '216000', 'Auditoria',               'Enero Junio 2026', 30, 'AUDIT01', $horarioLMV],
            [$i1, $d1, '216001', 'Sistemas de Informacion',  'Enero Junio 2026', 25, 'SISINF1', $horarioMAJ],
            [$i1, $d1, '216002', 'Base de Datos',            'Enero Junio 2026', 28, 'BDATOS1', $horarioLMV],
            [$i2, $d2, '301A',   'Calculo Diferencial',      'Enero Junio 2026', 35, 'CALCUL1', $horarioLMV],
            [$i2, $d2, '302B',   'Algebra Lineal',           'Enero Junio 2026', 30, 'ALGEBR1', $horarioMAJ],
            [$i2, $d2, '303C',   'Fisica Clasica',           'Enero Junio 2026', 32, 'FISIC01', $horarioLMV],
        ];

        $gids = [];
        foreach ($grupos as $g) {
            $gids[] = DB::table('grupos')->insertGetId([
                'id_institucion' => $g[0],
                'id_docente'     => $g[1],
                'nombre'         => $g[2],
                'materia'        => $g[3],
                'periodo'        => $g[4],
                'no_alumnos'     => $g[5],
                'codigo_inv'     => $g[6],
                'horario'        => $g[7],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // ─── INSCRIPCIONES ────────────────────────────────────────────────────
        $inscs = [
            // TecToluca
            [$als[0], $gids[0]], [$als[0], $gids[1]], [$als[0], $gids[2]], // Sofia: 3 grupos
            [$als[1], $gids[0]], [$als[1], $gids[1]],                       // Diego: 2 grupos
            [$als[2], $gids[0]], [$als[2], $gids[2]],                       // Valeria: 2 grupos
            [$als[3], $gids[1]], [$als[3], $gids[2]],                       // Andres: 2 grupos
            [$als[4], $gids[0]], [$als[4], $gids[1]], [$als[4], $gids[2]], // Camila: 3 grupos
            // UAEM
            [$als[5], $gids[3]], [$als[5], $gids[4]],                       // Luis: 2 grupos
            [$als[6], $gids[3]], [$als[6], $gids[5]],                       // Isabella: 2 grupos
            [$als[7], $gids[3]], [$als[7], $gids[4]], [$als[7], $gids[5]], // Sebastian: 3 grupos
            [$als[8], $gids[4]], [$als[8], $gids[5]],                       // Fernanda: 2 grupos
            [$als[9], $gids[3]], [$als[9], $gids[4]], [$als[9], $gids[5]], // Miguel: 3 grupos
        ];

        foreach ($inscs as $insc) {
            DB::table('grupo_alumnos')->insert([
                'id_grupo'        => $insc[1],
                'id_alumno'       => $insc[0],
                'fec_inscripcion' => $now->toDateString(),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ─── SESIONES Y ASISTENCIAS ───────────────────────────────────────────
        // Estados: 1=Presente, 2=Ausente, 3=Justificada

        $fechas = [
            Carbon::now()->subDays(35),
            Carbon::now()->subDays(30),
            Carbon::now()->subDays(25),
            Carbon::now()->subDays(20),
            Carbon::now()->subDays(15),
            Carbon::now()->subDays(10),
            Carbon::now()->subDays(5),
            Carbon::now()->subDays(2),
        ];

        // Patrones de asistencia por grupo (8 sesiones)
        $pat = [
            $gids[0] => [
                $als[0] => [1, 1, 1, 1, 1, 1, 1, 1], // Sofia    100% ✅
                $als[1] => [1, 1, 2, 1, 1, 2, 1, 1], // Diego    75%  ⚠ riesgo próximo
                $als[2] => [1, 2, 1, 2, 2, 1, 2, 1], // Valeria  50%  ❌ límite excedido
                $als[4] => [2, 2, 3, 1, 1, 1, 2, 1], // Camila   62.5% ⚠
            ],
            $gids[1] => [
                $als[0] => [1, 1, 1, 2, 1, 1, 1, 1], // Sofia    87.5% ✅
                $als[1] => [1, 1, 1, 1, 1, 2, 1, 1], // Diego    87.5% ✅
                $als[3] => [1, 2, 2, 1, 2, 1, 1, 1], // Andres   62.5% ⚠
                $als[4] => [1, 1, 1, 1, 1, 1, 1, 2], // Camila   87.5% ✅
            ],
            $gids[2] => [
                $als[0] => [1, 1, 1, 1, 1, 1, 1, 1], // Sofia    100% ✅
                $als[2] => [1, 1, 1, 1, 2, 1, 1, 1], // Valeria  87.5% ✅
                $als[3] => [2, 2, 2, 1, 1, 1, 2, 1], // Andres   50%  ❌
                $als[4] => [1, 3, 1, 1, 1, 1, 1, 1], // Camila   100% ✅ (1 justificada)
            ],
            $gids[3] => [
                $als[5] => [1, 1, 1, 1, 1, 1, 1, 1], // Luis      100% ✅
                $als[6] => [1, 2, 1, 1, 2, 1, 1, 1], // Isabella  75%  ⚠
                $als[7] => [2, 2, 2, 1, 1, 1, 2, 1], // Sebastian 50%  ❌
                $als[9] => [1, 1, 1, 1, 1, 2, 1, 1], // Miguel    87.5% ✅
            ],
            $gids[4] => [
                $als[5] => [1, 1, 2, 1, 1, 1, 1, 2], // Luis      75%  ⚠
                $als[7] => [1, 1, 1, 1, 1, 1, 1, 1], // Sebastian 100% ✅
                $als[8] => [2, 1, 2, 1, 1, 1, 1, 1], // Fernanda  75%  ⚠
                $als[9] => [1, 1, 1, 1, 3, 1, 1, 1], // Miguel    100% ✅ (1 justificada)
            ],
            $gids[5] => [
                $als[6] => [1, 1, 1, 1, 1, 1, 2, 1], // Isabella  87.5% ✅
                $als[7] => [1, 1, 1, 1, 1, 1, 1, 1], // Sebastian 100% ✅
                $als[8] => [1, 2, 1, 1, 2, 2, 1, 1], // Fernanda  62.5% ⚠
                $als[9] => [1, 1, 1, 2, 1, 1, 1, 1], // Miguel    87.5% ✅
            ],
        ];

        foreach ($pat as $grupoId => $alumnos) {
            foreach ($fechas as $i => $fecha) {
                $apertura = $fecha->copy()->setHour(8)->setMinute(0);
                $cierre   = $fecha->copy()->setHour(10)->setMinute(0);

                $sesId = DB::table('sesiones')->insertGetId([
                    'id_grupo'      => $grupoId,
                    'clave'         => null,
                    'est_sesion'    => 0,
                    'fec_sesion'    => $fecha->toDateString(),
                    'hora_apertura' => $apertura,
                    'hora_cierre'   => $cierre,
                    'created_at'    => $apertura,
                    'updated_at'    => $cierre,
                ]);

                foreach ($alumnos as $alumnoId => $patron) {
                    $est = $patron[$i];
                    DB::table('asistencias')->insert([
                        'id_sesion'      => $sesId,
                        'id_alumno'      => $alumnoId,
                        'est_asistencia' => $est,
                        'hora_registro'  => $est === 1 ? $apertura->copy()->addMinutes(rand(1, 15)) : null,
                        'created_at'     => $apertura,
                        'updated_at'     => $apertura,
                    ]);
                }
            }
        }

        // ─── SESIÓN ACTIVA PARA PRUEBAS EN VIVO ──────────────────────────────
        DB::table('sesiones')->insert([
            'id_grupo'      => $gids[0],
            'clave'         => 'PRUEBA',
            'est_sesion'    => 1,
            'fec_sesion'    => now()->toDateString(),
            'hora_apertura' => now(),
            'hora_cierre'   => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // ─── SUSCRIPCIONES ────────────────────────────────────────────────────
        // plan: 0=basico, 1=mensual — ambos docentes con Plan Mensual activo
        foreach ([$d1, $d2] as $docId) {
            DB::table('suscripciones')->insert([
                'id_usuario'      => $docId,
                'plan'            => 1,
                'est_suscripcion' => 1,
                'fec_inicio'      => now()->toDateString(),
                'fec_fin'         => Carbon::now()->addYears(10)->toDateString(),
                'fec_ultimo_pago' => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ─── RESUMEN EN CONSOLA ───────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ SistemaSeeder completado — Datos de prueba listos');
        $this->command->info('');
        $this->command->info('DOCENTES (contraseña: Omega2026)');
        $this->command->info('  cmendoza@omega.com   → TecToluca  (216000 Auditoria, 216001 SisInfo, 216002 BD)');
        $this->command->info('  lgutierrez@omega.com → UAEM       (301A Calculo, 302B Algebra, 303C Fisica)');
        $this->command->info('');
        $this->command->info('ALUMNOS (contraseña: Omega2026)');
        $this->command->info('  TecToluca: sramirez, dtorres, vcastro, aflores, cherrera @omega.com');
        $this->command->info('  UAEM:      lvargas, imorales, sruiz, fsalinas, mromero @omega.com');
        $this->command->info('');
        $this->command->info('PERIODOS');
        $this->command->info('  TecToluca → Enero Junio 2026 (activo), Agosto Diciembre 2025 (inactivo)');
        $this->command->info('  UAEM      → Enero Junio 2026 (activo), Agosto Diciembre 2025 (inactivo)');
        $this->command->info('');
        $this->command->info('SESIÓN ACTIVA: Grupo 216000 (Auditoria) — Clave: PRUEBA');
        $this->command->info('Alumnos en riesgo: dtorres (75%), vcastro (50%), aflores (50%), cherrera (62.5%)');
        $this->command->info('');
    }
}
