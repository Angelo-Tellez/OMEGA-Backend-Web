<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Models/GrupoAlumno.php
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
 * Modelo Eloquent — tabla: grupo_alumnos
 * Tabla intermedia N:M que registra la vinculación de un Alumno a un Grupo.
 */
class GrupoAlumno extends Model
{
    use HasFactory;

    protected $table = 'grupo_alumnos';
    protected $primaryKey = 'id_grupo_alumno';

    protected $fillable = [
        'id_grupo',
        'id_alumno',
        'fec_inscripcion',
    ];

    protected function casts(): array
    {
        return [
            'fec_inscripcion' => 'date',
        ];
    }

    // Relaciones
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'id_alumno', 'id_usuario');
    }
}