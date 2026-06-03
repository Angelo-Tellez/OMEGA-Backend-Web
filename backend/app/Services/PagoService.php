<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/PagoService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use App\Models\Pago;
use App\Models\Usuario;
use App\Repositories\Contracts\PagoRepositoryInterface;
use App\Repositories\Contracts\SuscripcionRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Service — Integración con PayPal Sandbox.
 * Gestiona la creación y captura de órdenes de pago.
 */
class PagoService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct(
        private readonly PagoRepositoryInterface       $pagos,
        private readonly SuscripcionRepositoryInterface $suscripciones,
        private readonly SuscripcionService            $suscripcionService,
    ) {
        $this->baseUrl      = config('paypal.base_url');
        $this->clientId     = config('paypal.client_id');
        $this->clientSecret = config('paypal.client_secret');
    }

    /**
     * Paso 1: Crear orden en PayPal — devuelve el order_id para el frontend
     */
    public function crearOrden(Usuario $docente): array
    {
        $token = $this->obtenerToken();

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'MXN',
                        'value'         => '149.00',
                    ],
                    'description' => 'Plan Mensual - Control de Asistencias',
                ]],
                'application_context' => [
                'return_url' => config('app.url') . '/p/ca/suscripcion/capturar',
                'cancel_url' => config('app.url') . '/p/ca/suscripcion/cancelar',
                    'brand_name' => 'OMEGA Control de Asistencias',
                    'user_action' => 'PAY_NOW',
                    'landing_page' => 'LOGIN',
                ],
            ]);

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'paypal' => ['Error al crear la orden en PayPal.'],
            ]);
        }

        $orden = $response->json();

        // Obtener o crear suscripción
        $suscripcion = $this->suscripciones->buscarPorUsuario($docente->id_usuario)
            ?? $this->suscripcionService->crearPlanBasico($docente);

        // Registrar el pago como PENDING
        $this->pagos->crear([
            'id_suscripcion'        => $suscripcion->id_suscripcion,
            'paypal_order_id'       => $orden['id'],
            'paypal_transaction_id' => null,
            'mon_monto'             => 149.00,
            'est_pago'              => 'PENDING',
            'fec_pago'              => now()->toDateString(),
            'tipo_pago'             => 'paypal',
        ]);

        return [
            'order_id'    => $orden['id'],
            'approval_url' => collect($orden['links'])
                ->firstWhere('rel', 'approve')['href'] ?? null,
        ];
    }

    /**
     * Paso 2: Capturar el pago después de que el usuario aprueba en PayPal
     */
    public function capturarPago(string $orderId, Usuario $docente): array
    {
        $pago = $this->pagos->buscarPorOrderId($orderId);

        if (!$pago) {
            throw ValidationException::withMessages([
                'order_id' => ['Orden de pago no encontrada.'],
            ]);
        }

        $token = $this->obtenerToken();

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Content-Length' => '0',
            ])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture", null);
            
        if (!$response->successful()) {
            $this->pagos->guardar($pago, ['est_pago' => 'FAILED']);
            throw ValidationException::withMessages([
                'paypal' => ['Error captura PayPal: ' . $response->body()],
            ]);
        }

        $captura       = $response->json();
        $transaccion   = $captura['purchase_units'][0]['payments']['captures'][0] ?? null;
        $transaccionId = $transaccion['id'] ?? null;
        $estado        = $transaccion['status'] ?? 'FAILED';

        // Actualizar el pago
        $this->pagos->guardar($pago, [
            'paypal_transaction_id' => $transaccionId,
            'est_pago'              => $estado === 'COMPLETED' ? 'COMPLETED' : 'FAILED',
        ]);

        if ($estado !== 'COMPLETED') {
            throw ValidationException::withMessages([
                'paypal' => ['El pago no fue completado.'],
            ]);
        }

        // Activar plan mensual
        $this->suscripcionService->activarPlanMensual($docente, []);

        return $this->serializar($pago->fresh());
    }

    public function historial(Usuario $docente): array
    {
        $suscripcion = $this->suscripciones->buscarPorUsuario($docente->id_usuario);

        if (!$suscripcion) return [];

        return $this->pagos->todosPorSuscripcion($suscripcion->id_suscripcion)
            ->map(fn(Pago $p) => $this->serializar($p))
            ->values()
            ->all();
    }

    private function obtenerToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'paypal' => ['Error PayPal: ' . $response->body()],
            ]);
        }

        return $response->json('access_token');
    }

    private function serializar(Pago $pago): array
    {
        return [
            'id_pago'               => $pago->id_pago,
            'id_suscripcion'        => $pago->id_suscripcion,
            'paypal_order_id'       => $pago->paypal_order_id,
            'paypal_transaction_id' => $pago->paypal_transaction_id,
            'mon_monto'             => $pago->mon_monto,
            'est_pago'              => $pago->est_pago,
            'fec_pago'              => $pago->fec_pago?->toDateString(),
            'tipo_pago'             => $pago->tipo_pago,
        ];
    }
}