<?php

/*
 * ============================================================
 * Modelo Eloquent — tabla: periodos
 * MDB-OMEGA-03 §4.1 | MPL-OMEGA-05 §6.3
 * Representa los periodos academicos por institucion.
 * @version 1.0.0
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table      = 'periodos';
    protected $primaryKey = 'id_periodo';

    protected $fillable = [
        'id_institucion',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }
}
