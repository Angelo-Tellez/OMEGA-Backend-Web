<?php

namespace App\Http\Controllers;

use App\Services\PagoService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\CapturarPagoRequest;
use Illuminate\Http\Request;

/**
 * Controlador HTTP — Integración con PayPal Sandbox.
 * Sin lógica de negocio, solo delega al PagoService.
 */
class PagoController extends Controller
{
    public function __construct(
        private readonly PagoService $pagos
    ) {}

    public function crearOrden(Request $request): JsonResponse
    {
        $orden = $this->pagos->crearOrden($request->user());
        return response()->json(['data' => $orden, 'message' => 'El registro se realizó correctamente'], 201);
    }

    public function capturarPago(CapturarPagoRequest $request): JsonResponse
    {
        $pago = $this->pagos->capturarPago($request->order_id, $request->user());
        return response()->json(['data' => $pago, 'message' => 'La información se cargó correctamente']);
    }

    public function historial(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->pagos->historial($request->user()),
        ]);
    }

    /**
     * PayPal redirige aquí desde la app móvil después de aprobar.
     * La app detecta la URL con 'PayerID' y la intercepta en el WebView.
     * Si no es interceptada, redirigimos a la web.
     */
    public function cancelarPago(Request $request): JsonResponse
    {
        // El pago fue cancelado por el usuario — no hay acción en el backend
        return response()->json(['data' => ['cancelado' => true], 'message' => 'La operación se canceló correctamente']);
    }

    public function paypalReturn(Request $request)
    {
        // Si viene con token (order_id) redirigimos a la web para capturar
        $token = $request->query('token');
        if ($token) {
            return redirect()->route('ca.suscripcion.capturar', ['token' => $token]);
        }
        return redirect()->route('ca.suscripcion.index');
    }

    public function paypalCancel()
    {
        return redirect()->route('ca.suscripcion.cancelar');
    }
}