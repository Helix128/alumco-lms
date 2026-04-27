<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => 'Curso: ' . $this->faker->catchPhrase(),
            'descripcion' => $this->faker->paragraph(3),
            'capacitador_id' => User::factory(),
        ];
    }
}