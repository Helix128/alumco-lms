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
        'capacitador_id',
        'es_secuencial',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'es_secuencial' => 'boolean',
    ];

    // --- RELACIONES ---

    public function capacitador()
    {
        return $this->belongsTo(User::class, 'capacitador_id');
    }

    public function estamentos()
    {
        return $this->belongsToMany(Estamento::class);
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class)->orderBy('orden');
    }

    // --- LÓGICA DE NEGOCIO ---

    public function estaDisponible(): bool
    {
        $hoy = now();
        return $this->fecha_inicio <= $hoy && $this->fecha_fin >= $hoy;
    }

    public function progresoParaUsuario(User $user): int
    {
        $total = $this->modulos->count();

        if ($total === 0) {
            return 0;
        }

        $completados = $this->modulos
            ->filter(fn(Modulo $m) => $m->estaCompletadoPor($user))
            ->count();

        return (int) round(($completados / $total) * 100);
    }
}
