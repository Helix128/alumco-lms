<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportePreset extends Model
{
    protected $fillable = [
        'nombre',
        'columnas',
    ];

    protected function casts(): array
    {
        return [
            'columnas' => 'array',
        ];
    }
}
