<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Models/Grupo.php
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
class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'id_institucion',
        'id_docente',
        'nombre',
        'materia',
        'periodo',
        'no_alumnos',
        'codigo_inv',
        'horario',
    ];

    /** MPL §6.3 — Ocultar codigo_inv en respuestas JSON (medida defensiva) */
    protected $hidden = [
        'codigo_inv',
    ];

    protected function casts(): array
    {
        return [
            'no_alumnos' => 'integer',
            'horario'    => 'array',
        ];
    }

    // ─── Query Scopes (MPL §2.3.1) ───────────────────────────────────────────

    /** Filtra grupos pertenecientes a un docente específico. */
    public function scopePorDocente($query, int $idDocente)
    {
        return $query->where('id_docente', $idDocente);
    }

    /** Filtra grupos pertenecientes a una institución específica. */
    public function scopePorInstitucion($query, int $idInstitucion)
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function docente()
    {
        return $this->belongsTo(Usuario::class, 'id_docente', 'id_usuario');
    }

    public function grupoAlumnos()
    {
        return $this->hasMany(GrupoAlumno::class, 'id_grupo', 'id_grupo');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'id_grupo', 'id_grupo');
    }

    public function alumnos()
    {
        return $this->belongsToMany(
            Usuario::class,
            'grupo_alumnos',
            'id_grupo',
            'id_alumno',
            'id_grupo',
            'id_usuario'
        );
    }
}