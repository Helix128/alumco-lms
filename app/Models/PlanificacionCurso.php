<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanificacionCurso extends Model
{
    protected $table = 'planificaciones_cursos';

    protected $fillable = [
        'curso_id',
        'sede_id',
        'fecha_inicio',
        'fecha_fin',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // --- RELACIONES ---

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    // --- LÓGICA ---

    public function estaActivo(): bool
    {
        $hoy = now()->startOfDay();

        return $this->fecha_inicio->lte($hoy) && $this->fecha_fin->gte($hoy);
    }
}
