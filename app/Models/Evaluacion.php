<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluaciones';

    protected $fillable = ['modulo_id'];

    public function getPuntosAprobacionAttribute(): int
    {
        $porcentaje = (int) GlobalSetting::get('evaluacion_puntos_aprobacion', 70);
        $totalPreguntas = $this->relationLoaded('preguntas')
            ? $this->preguntas->count()
            : $this->preguntas()->count();

        if ($totalPreguntas === 0) {
            return 1;
        }

        return max(0, (int) ceil($totalPreguntas * ($porcentaje / 100)));
    }

    public function getMaxIntentosSemanalesAttribute(): int
    {
        return (int) GlobalSetting::get('evaluacion_max_intentos_semanales', 3);
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class)->orderBy('orden');
    }

    public function intentos()
    {
        return $this->hasMany(IntentoEvaluacion::class);
    }
}
