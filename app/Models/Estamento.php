<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estamento extends Model
{
    use SoftDeletes;

    // Permitir la asignación masiva del campo nombre
    protected $fillable = ['nombre'];

    // Relación: Un estamento tiene muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relación: Un estamento tiene acceso a muchos cursos
    public function cursos()
    {
        return $this->belongsToMany(Curso::class);
    }
}