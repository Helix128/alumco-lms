<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory; // <-- Clave para que el Factory funcione

    // Permisos de asignación masiva
    protected $fillable = [
        'curso_id',
        'titulo',
        'orden',
        'tipo_contenido',
        'ruta_archivo'
    ];

    // Relación: Un módulo pertenece a un curso
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}