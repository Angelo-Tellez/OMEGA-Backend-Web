<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Models/Asistencia.php
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
 * Modelo Eloquent — tabla: asistencias
 * Registro unitario de la asistencia de un Alumno en una Sesión.
 * est_asistencia: 1 = Presente, 2 = Ausente, 3 = Justificada
 */
class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';
    protected $primaryKey = 'id_asistencia';

    protected $fillable = [
        'id_sesion',
        'id_alumno',
        'est_asistencia',
        'hora_registro',
    ];

    protected function casts(): array
    {
        return [
            'est_asistencia' => 'integer',
            'hora_registro'  => 'datetime',
        ];
    }

    // Relaciones
    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'id_sesion', 'id_sesion');
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'id_alumno', 'id_usuario');
    }
}