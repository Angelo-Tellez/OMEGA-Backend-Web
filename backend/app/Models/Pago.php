<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Models/Pago.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent — tabla: pagos
 * Registro histórico de transacciones procesadas por PayPal.
 * est_pago: COMPLETED, PENDING, FAILED, REFUNDED
 */
class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_suscripcion',
        'paypal_order_id',
        'paypal_transaction_id',
        'mon_monto',
        'est_pago',
        'fec_pago',
        'tipo_pago',
    ];

    protected function casts(): array
    {
        return [
            'mon_monto' => 'double',
            'fec_pago'  => 'date',
        ];
    }

    // Relaciones
    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'id_suscripcion', 'id_suscripcion');
    }
}