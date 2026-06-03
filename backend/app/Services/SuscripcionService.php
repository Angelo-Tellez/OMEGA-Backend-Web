<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/SuscripcionService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Suscripcion;
use App\Models\Usuario;
use App\Repositories\Contracts\SuscripcionRepositoryInterface;
use Illuminate\Validation\ValidationException;

/**
 * Service — Gestión del plan activo del Docente.
 * plan: 1 = Básico (gratuito), 2 = Mensual ($149 MXN)
 * est_suscripcion: 1 = Activa, 2 = Vencida, 3 = En gracia (72h)
 */
class SuscripcionService
{
    public function __construct(
        private readonly SuscripcionRepositoryInterface $suscripciones,
    ) {}

    public function obtener(Usuario $docente): array
    {
        $suscripcion = $this->suscripciones->buscarPorUsuario($docente->id_usuario);

        if (!$suscripcion) {
            // Si no tiene suscripción, se crea una en plan básico automáticamente
            $suscripcion = $this->crearPlanBasico($docente);
        }

        return $this->serializar($suscripcion);
    }

    public function crearPlanBasico(Usuario $docente): Suscripcion
    {
        // Verificar que no tenga ya una suscripción
        $existe = $this->suscripciones->buscarPorUsuario($docente->id_usuario);
        if ($existe) {
            return $existe;
        }

        return $this->suscripciones->crear([
            'id_usuario'      => $docente->id_usuario,
            'plan'            => 1, // Básico
            'est_suscripcion' => 1, // Activa
            'fec_inicio'      => now()->toDateString(),
            'fec_fin'         => now()->addYears(100)->toDateString(), // Plan básico no vence
            'fec_ultimo_pago' => null,
        ]);
    }

    public function activarPlanMensual(Usuario $docente, array $datosPago): array
    {
        $suscripcion = $this->suscripciones->buscarPorUsuario($docente->id_usuario);

        if (!$suscripcion) {
            $suscripcion = $this->crearPlanBasico($docente);
        }

        $this->suscripciones->guardar($suscripcion, [
            'plan'            => 2, // Mensual
            'est_suscripcion' => 1, // Activa
            'fec_inicio'      => now()->toDateString(),
            'fec_fin'         => now()->addMonth()->toDateString(),
            'fec_ultimo_pago' => now()->toDateString(),
        ]);

        return $this->serializar($suscripcion->fresh());
    }

    public function verificarAccesoPremium(Usuario $docente): bool
    {
        $suscripcion = $this->suscripciones->buscarPorUsuario($docente->id_usuario);

        if (!$suscripcion) return false;

        // Plan mensual activo o en gracia
        return $suscripcion->plan === 2 &&
               in_array($suscripcion->est_suscripcion, [1, 3]);
    }

    public function verificarGracia(Suscripcion $suscripcion): void
    {
        if ($suscripcion->plan !== 2) return;
        if ($suscripcion->est_suscripcion !== 1) return;

        $fecFin = $suscripcion->fec_fin;
        if (now()->greaterThan($fecFin)) {
            $horasVencida = now()->diffInHours($fecFin);
            $nuevoEstado = $horasVencida <= 72 ? 3 : 2; // 3=En gracia, 2=Vencida
            $this->suscripciones->guardar($suscripcion, [
                'est_suscripcion' => $nuevoEstado,
            ]);
        }
    }

    private function serializar(Suscripcion $suscripcion): array
    {
        return [
            'id_suscripcion'  => $suscripcion->id_suscripcion,
            'id_usuario'      => $suscripcion->id_usuario,
            'plan'            => $suscripcion->plan,
            'plan_nombre'     => $suscripcion->plan === 1 ? 'Básico' : 'Mensual',
            'est_suscripcion' => $suscripcion->est_suscripcion,
            'est_nombre'      => match($suscripcion->est_suscripcion) {
                1 => 'Activa',
                2 => 'Vencida',
                3 => 'En gracia',
                default => 'Desconocido',
            },
            'fec_inicio'      => $suscripcion->fec_inicio?->toDateString(),
            'fec_fin'         => $suscripcion->fec_fin?->toDateString(),
            'fec_ultimo_pago' => $suscripcion->fec_ultimo_pago?->toDateString(),
        ];
    }
}