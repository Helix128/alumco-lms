<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionCurso extends Model
{
    use HasFactory;

    protected $table = 'seccion_cursos';

    protected $fillable = [
        'curso_id',
        'titulo',
        'orden',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class, 'seccion_id')->orderBy('orden');
    }
}
