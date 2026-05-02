<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => 'Curso: '.$this->faker->catchPhrase(),
            'descripcion' => $this->faker->paragraph(3),
            'imagen_portada' => null,
            'color_promedio' => null,
            'capacitador_id' => User::factory(),
        ];
    }

    public function withCover(string $path, string $color = '#205099'): static
    {
        return $this->state(fn (array $attributes): array => [
            'imagen_portada' => $path,
            'color_promedio' => $color,
        ]);
    }
}
