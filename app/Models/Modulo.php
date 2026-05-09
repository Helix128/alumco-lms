<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    const TIPOS = ['video', 'pdf', 'ppt', 'texto', 'imagen', 'evaluacion'];

    const TIPO_LABELS = [
        'video' => 'video',
        'pdf' => 'documento',
        'ppt' => 'presentación',
        'texto' => 'texto',
        'imagen' => 'imagen',
        'evaluacion' => 'evaluación',
    ];

    protected $fillable = [
        'curso_id',
        'seccion_id',
        'titulo',
        'orden',
        'tipo_contenido',
        'ruta_archivo',
        'nombre_archivo_original',
        'contenido',
        'duracion_minutos',
    ];

    // --- RELACIONES ---

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function seccion()
    {
        return $this->belongsTo(SeccionCurso::class, 'seccion_id');
    }

    public function evaluacion()
    {
        return $this->hasOne(Evaluacion::class);
    }

    public function progresos()
    {
        return $this->hasMany(ProgresoModulo::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
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
        // Si el módulo ya está completado, siempre es accesible
        if ($this->estaCompletadoPor($user)) {
            return true;
        }

        // Buscar el módulo anterior basado en el orden global del curso
        $anterior = $curso->modulos->where('orden', '<', $this->orden)->last();

        if (! $anterior) {
            return true;
        }

        return $anterior->estaCompletadoPor($user);
    }
}
