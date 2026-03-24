<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_portada',
        'fecha_inicio',
        'fecha_fin',
        'capacitador_id'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // --- RELACIONES ---

    public function capacitador()
    {
        return $this->belongsTo(User::class , 'capacitador_id');
    }

    // ¡ESTA ES LA FUNCIÓN QUE FALTABA Y CAUSABA EL ERROR!
    public function estamentos()
    {
        return $this->belongsToMany(Estamento::class);
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class)->orderBy('orden');
    }

    // Lógica de negocio encapsulada
    public function estaDisponible()
    {
        $hoy = now();
        return $this->fecha_inicio <= $hoy && $this->fecha_fin >= $hoy;
    }
}