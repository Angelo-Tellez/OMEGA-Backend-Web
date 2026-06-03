<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : backend/app/Models/Usuario.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'ap_pat',
        'ap_mat',
        'email',
        'contrasenia',
        'rol',
    ];

    protected $hidden = [
        'contrasenia',
    ];

    /**
     * Alias para que Laravel Sanctum/Auth use 'contrasenia' como 'password'.
     * (MPL-OMEGA-05 §6.3 — Manejo de Excepciones, autenticación segura)
     */
    public function getAuthPassword(): string
    {
        return $this->contrasenia;
    }

    /**
     * Nombre del campo de contraseña para que Laravel no intente
     * hacer rehash en la columna 'password' inexistente.
     */
    public function getAuthPasswordName(): string
    {
        return 'contrasenia';
    }

    protected function casts(): array
    {
        return [
            'rol'         => 'integer',
            'contrasenia' => 'hashed',
        ];
    }

    // ─── Helpers de rol ───────────────────────────────────────────────────

    /** RF-10 — Verifica si el usuario es Docente (rol = 1). */
    public function isDocente(): bool
    {
        return $this->rol === 1;
    }

    /** RF-10 — Verifica si el usuario es Alumno (rol = 2). */
    public function isAlumno(): bool
    {
        return $this->rol === 2;
    }

    // ─── Relaciones ───────────────────────────────────────────────────────

    /** Instituciones del docente (RF-57, RF-60). */
    public function instituciones()
    {
        return $this->hasMany(Institucion::class, 'id_docente', 'id_usuario');
    }

    /** Grupos del docente (RF-58). */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'id_docente', 'id_usuario');
    }

    /** Suscripción activa del docente (RF-79, RF-80). */
    public function suscripcion()
    {
        return $this->hasOne(Suscripcion::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Vinculaciones del alumno a grupos (RF-11, RF-19).
     * Tabla intermedia: grupo_alumnos (MDB-OMEGA-DD-01 §4.5)
     */
    public function grupoAlumnos()
    {
        return $this->hasMany(GrupoAlumno::class, 'id_alumno', 'id_usuario');
    }

    /** Asistencias del alumno (RF-31, RF-32). */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_alumno', 'id_usuario');
    }
}
