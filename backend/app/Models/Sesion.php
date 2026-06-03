<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent — tabla: sesiones
 * Representa cada apertura de ventana de registro de asistencia.
 * est_sesion: 1 = Activa, 0 = Cerrada
 */
class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesiones';
    protected $primaryKey = 'id_sesion';

    protected $fillable = [
        'id_grupo',
        'clave',
        'est_sesion',
        'fec_sesion',
        'hora_apertura',
        'hora_cierre',
    ];

    protected function casts(): array
    {
        return [
            'est_sesion'    => 'integer',
            'fec_sesion'    => 'date',
            'hora_apertura' => 'datetime',
            'hora_cierre'   => 'datetime',
        ];
    }

    // ─── Query Scopes (MPL §2.3.1) ───────────────────────────────────────────

    /** Filtra sesiones por grupo. */
    public function scopePorGrupo($query, int $idGrupo)
    {
        return $query->where('id_grupo', $idGrupo);
    }

    /** Filtra sesiones activas (est_sesion = 1). */
    public function scopeActivas($query)
    {
        return $query->where('est_sesion', 1);
    }

    /** Filtra sesiones cerradas (est_sesion = 0). */
    public function scopeCerradas($query)
    {
        return $query->where('est_sesion', 0);
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_sesion', 'id_sesion');
    }
}