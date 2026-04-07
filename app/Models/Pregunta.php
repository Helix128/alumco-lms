<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    protected $fillable = ['evaluacion_id', 'enunciado', 'orden'];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function opciones()
    {
        return $this->hasMany(Opcion::class)->orderBy('orden');
    }
}
