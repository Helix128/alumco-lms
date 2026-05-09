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
            'El trabajador no ha aprobado ninguna evaluacion de este curso.'
        );
    }

    public function publicMessage(): string
    {
        return 'El certificado no se puede generar porque el trabajador aun no aprueba la evaluacion requerida del curso.';
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
