<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntentoEvaluacion extends Model
{
    protected $table = 'intentos_evaluacion';

    protected $fillable = [
        'user_id',
        'evaluacion_id',
        'puntaje',
        'total_preguntas',
        'aprobado',
    ];

    protected $casts = ['aprobado' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestaEvaluacion::class, 'intento_id');
    }
}
