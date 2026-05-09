<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    public const TipoCurso = 'curso';

    public const TipoPlataforma = 'plataforma';

    public const EstadoNuevo = 'nuevo';

    public const EstadoRevisado = 'revisado';

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'curso_id',
        'modulo_id',
        'tipo',
        'categoria',
        'rating',
        'mensaje',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }
}
