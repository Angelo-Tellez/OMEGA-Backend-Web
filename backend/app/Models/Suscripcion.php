<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Models/Suscripcion.php
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
 * Modelo Eloquent — tabla: suscripciones
 * Gestiona el plan activo de cada Docente.
 * plan: 1 = Básico, 2 = Mensual
 * est_suscripcion: 1 = Activa, 2 = Vencida, 3 = En gracia
 */
class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';
    protected $primaryKey = 'id_suscripcion';

    protected $fillable = [
        'id_usuario',
        'plan',
        'est_suscripcion',
        'fec_inicio',
        'fec_fin',
        'fec_ultimo_pago',
    ];

    protected function casts(): array
    {
        return [
            'plan'            => 'integer',
            'est_suscripcion' => 'integer',
            'fec_inicio'      => 'date',
            'fec_fin'         => 'date',
            'fec_ultimo_pago' => 'date',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_suscripcion', 'id_suscripcion');
    }
}