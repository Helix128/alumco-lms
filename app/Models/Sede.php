<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sede extends Model
{
    use SoftDeletes;

    // Permitir la asignación masiva del campo nombre
    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function planificaciones()
    {
        return $this->hasMany(PlanificacionCurso::class);
    }
}