<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/SuscripcionWebController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PagoService;
use App\Services\SuscripcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador Web — Suscripción y pagos PayPal.
 * RF-07: Gestión de planes y pagos desde la web.
 */
class SuscripcionWebController extends Controller
{
    public function __construct(
        private readonly SuscripcionService $suscripciones,
        private readonly PagoService        $pagos,
    ) {}

    public function index(Request $request)
    {
        $suscripcion = $this->suscripciones->obtener(Auth::user());

        $status = $request->query('status');

        return view('modules.suscripcion.index', compact('suscripcion', 'status'));
    }

    /**
     * Crea la orden de PayPal y redirige al usuario a PayPal para aprobar.
     */
    public function crearOrden(Request $request)
    {
        try {
            $orden = $this->pagos->crearOrden(Auth::user());

            if ($orden['approval_url']) {
                return redirect($orden['approval_url']);
            }

            return redirect()->route('ca.suscripcion.index')
                ->with('error', 'No se pudo obtener el enlace de PayPal');
        } catch (\Exception $e) {
            Log::error('[OMEGA] ' . class_basename($this) . ': ' . $e->getMessage());
            return redirect()->route('ca.suscripcion.index')
                ->with('error', 'Error al conectar con PayPal: ' . $e->getMessage());
        }
    }

    /**
     * PayPal redirige aquí después de que el usuario aprueba el pago.
     * RF-07: Captura el pago y actualiza la suscripción.
     */
    public function capturarPago(Request $request)
    {
        $orderId = $request->query('token'); // PayPal manda el token en la URL

        if (!$orderId) {
            return redirect()->route('ca.suscripcion.index')
                ->with('error', 'No se recibió confirmación de PayPal');
        }

        try {
            $this->pagos->capturarPago($orderId, Auth::user());

            return redirect()->route('ca.suscripcion.index')
                ->with('success', '¡Pago exitoso! Tu Plan Mensual ha sido activado.');
        } catch (\Exception $e) {
            Log::error('[OMEGA] ' . class_basename($this) . ': ' . $e->getMessage());
            return redirect()->route('ca.suscripcion.index')
                ->with('error', 'Error al capturar el pago: ' . $e->getMessage());
        }
    }

    /**
     * PayPal redirige aquí si el usuario cancela.
     */
    public function cancelarPago()
    {
        return redirect()->route('ca.suscripcion.index')
            ->with('error', 'Pago cancelado. Tu plan no ha sido modificado.');
    }
}
