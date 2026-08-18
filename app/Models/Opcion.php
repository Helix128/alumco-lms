<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'opciones';

    protected $fillable = ['pregunta_id', 'texto', 'es_correcta', 'orden'];

    protected $casts = ['es_correcta' => 'boolean'];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }
}
