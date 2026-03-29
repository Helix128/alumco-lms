<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgresoModulo extends Model
{
    protected $table = 'progresos_modulo'; // Nombre exacto de la tabla
    protected $fillable = ['user_id', 'modulo_id', 'completado', 'fecha_completado'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }
}