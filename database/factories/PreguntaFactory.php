<?php

namespace Database\Factories;

use App\Models\Evaluacion;
use App\Models\Pregunta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pregunta>
 */
class PreguntaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'evaluacion_id' => Evaluacion::factory(),
            'enunciado' => $this->faker->sentence().'?',
            'orden' => 1,
        ];
    }
}
