<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportePreset extends Model
{
    use SoftDeletes;

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
