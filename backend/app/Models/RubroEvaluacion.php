<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Models/RubroEvaluacion.php
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
 * Modelo Eloquent — tabla: rubros_evaluacion
 * Define criterios mínimos de asistencia por institución.
 */
class RubroEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'rubros_evaluacion';
    protected $primaryKey = 'id_rubro';

    protected $fillable = [
        'id_institucion',
        'nombre',
        'porcentaje_minimo',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_minimo' => 'decimal:2',
        ];
    }

    // Relaciones
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }
}