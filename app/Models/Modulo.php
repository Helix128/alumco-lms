<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    const TIPOS = ['video', 'pdf', 'ppt', 'texto', 'imagen', 'evaluacion'];

    const TIPO_LABELS = [
        'video'      => 'video',
        'pdf'        => 'documento',
        'ppt'        => 'presentación',
        'texto'      => 'texto',
        'imagen'     => 'imagen',
        'evaluacion' => 'evaluación',
    ];

    protected $fillable = [
        'curso_id',
        'titulo',
        'orden',
        'tipo_contenido',
        'ruta_archivo',
        'contenido',
        'duracion_minutos',
    ];

    // --- RELACIONES ---

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function evaluacion()
    {
        return $this->hasOne(Evaluacion::class);
    }

    public function progresos()
    {
        return $this->hasMany(ProgresoModulo::class);
    }

    // --- MÉTODOS HELPER ---

    public function estaCompletadoPor(User $user): bool
    {
        // Usa relación cargada para evitar queries adicionales en bucles
        if ($this->relationLoaded('progresos')) {
            return $this->progresos
                ->where('user_id', $user->id)
                ->where('completado', true)
                ->isNotEmpty();
        }

        return $this->progresos()
            ->where('user_id', $user->id)
            ->where('completado', true)
            ->exists();
    }

    public function estaAccesiblePara(User $user, Curso $curso): bool
    {
        if (!$curso->es_secuencial) {
            return true;
        }

        $anterior = $curso->modulos->where('orden', '<', $this->orden)->last();

        if (!$anterior) {
            return true;
        }

        return $anterior->estaCompletadoPor($user);
    }
}
