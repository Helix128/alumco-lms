<?php

namespace App\Exceptions;

use App\Models\Curso;
use App\Models\User;
use RuntimeException;

class CertificateNotEligible extends RuntimeException
{
    public function __construct(
        private readonly int $userId,
        private readonly int $cursoId,
        string $message
    ) {
        parent::__construct($message);
    }

    public static function missingApprovedEvaluation(User $user, Curso $curso): self
    {
        return new self(
            $user->id,
            $curso->id,
            'El colaborador o colaboradora no ha aprobado ninguna evaluacion de esta capacitacion.'
        );
    }

    public function publicMessage(): string
    {
        return 'El certificado no se puede generar porque el colaborador o colaboradora aun no aprueba la evaluacion requerida de la capacitacion.';
    }

    /**
     * @return array{user_id: int, curso_id: int}
     */
    public function context(): array
    {
        return [
            'user_id' => $this->userId,
            'curso_id' => $this->cursoId,
        ];
    }
}
