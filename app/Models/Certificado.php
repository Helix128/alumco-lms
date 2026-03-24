<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $fillable = [
        'user_id',
        'curso_id',
        'codigo_verificacion',
        'ruta_pdf',
        'fecha_emision'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}