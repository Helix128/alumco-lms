<?php

namespace Database\Factories;

use App\Models\Opcion;
use App\Models\Pregunta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opcion>
 */
class OpcionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pregunta_id' => Pregunta::factory(),
            'texto' => $this->faker->sentence(),
            'es_correcta' => false,
            'orden' => 1,
        ];
    }

    public function correcta(): static
    {
        return $this->state(fn (array $attributes): array => [
            'es_correcta' => true,
        ]);
    }
}
