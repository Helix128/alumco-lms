<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = ['modulo_id'];

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
