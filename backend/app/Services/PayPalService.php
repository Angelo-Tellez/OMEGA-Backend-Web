<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Services/PayPalService.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Servicio PayPal — Sandbox.
 * Encapsula autenticacion OAuth2 y operaciones de orden (Orders v2 API).
 */
class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $secret;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.paypal.base_url'), '/');
        $this->clientId = config('services.paypal.client_id');
        $this->secret   = config('services.paypal.secret');
    }

    // ── TOKEN ────────────────────────────────────────────────

    private function obtenerToken(): string
    {
        $response = Http::withoutVerifying()
            ->withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('No se pudo obtener token de PayPal: ' . $response->body());
        }

        return $response->json('access_token');
    }

    // ── CREAR ORDEN ──────────────────────────────────────────

    /**
     * Crea una orden de PayPal y devuelve el order_id y la approval_url.
     *
     * @return array{order_id: string, approval_url: string}
     */
    public function crearOrden(float $monto, string $moneda = 'MXN'): array
    {
        $token = $this->obtenerToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent'         => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $moneda,
                            'value'         => number_format($monto, 2, '.', ''),
                        ],
                        'description' => 'Plan Mensual ATN',
                    ],
                ],
                'application_context' => [
                    'return_url'          => config('services.paypal.return_url'),
                    'cancel_url'          => config('services.paypal.cancel_url'),
                    'brand_name'          => 'ATN - Control de Asistencias',
                    'user_action'         => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Error al crear orden PayPal: ' . $response->body());
        }

        $data        = $response->json();
        $approvalUrl = collect($data['links'])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approvalUrl) {
            throw new RuntimeException('PayPal no retorno approval_url.');
        }

        return [
            'order_id'     => $data['id'],
            'approval_url' => $approvalUrl,
        ];
    }

    // ── CAPTURAR ORDEN ───────────────────────────────────────

    /**
     * Captura el pago de una orden aprobada por el usuario.
     *
     * @return array<string, mixed>
     */
    public function capturarOrden(string $orderId): array
    {
        $token = $this->obtenerToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->withBody('{}', 'application/json')
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if (!$response->successful()) {
            throw new RuntimeException('Error al capturar orden PayPal: ' . $response->body());
        }

        $data          = $response->json();
        $capture       = $data['purchase_units'][0]['payments']['captures'][0] ?? [];
        $transactionId = $capture['id'] ?? null;

        return [
            'order_id'       => $data['id'],
            'status'         => $data['status'],            // COMPLETED
            'transaction_id' => $transactionId,
            'monto'          => $capture['amount']['value'] ?? null,
            'moneda'         => $capture['amount']['currency_code'] ?? null,
        ];
    }
}
