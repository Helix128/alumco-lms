<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_portada',
        'capacitador_id',
        'curso_original_id',
    ];

    protected $casts = [
    ];

    // --- RELACIONES ---

    public function capacitador()
    {
        return $this->belongsTo(User::class, 'capacitador_id');
    }

    public function cursoOriginal()
    {
        return $this->belongsTo(Curso::class, 'curso_original_id');
    }

    public function versionesDerivadas()
    {
        return $this->hasMany(Curso::class, 'curso_original_id');
    }

    public function estamentos()
    {
        return $this->belongsToMany(Estamento::class);
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class)->orderBy('orden');
    }

    public function planificaciones(): HasMany
    {
        return $this->hasMany(PlanificacionCurso::class);
    }

    // --- LÓGICA DE NEGOCIO ---

    public function estaDisponible(): bool
    {
        $hoy = now()->startOfDay();
        return $this->planificaciones()
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->exists();
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
