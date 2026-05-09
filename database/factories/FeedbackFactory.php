<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'curso_id' => Curso::factory(),
            'modulo_id' => null,
            'tipo' => Feedback::TipoCurso,
            'categoria' => 'contenido',
            'rating' => $this->faker->numberBetween(1, 5),
            'mensaje' => 'La capsula fue clara, pero falta un ejemplo aplicado al turno clinico.',
            'estado' => Feedback::EstadoNuevo,
        ];
    }

    public function plataforma(): static
    {
        return $this->state(fn (array $attributes): array => [
            'curso_id' => null,
            'tipo' => Feedback::TipoPlataforma,
            'categoria' => 'sugerencia',
            'rating' => null,
        ]);
    }
}
