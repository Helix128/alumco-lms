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
            'fecha_inicio' => now()->subDays(rand(1, 10)), // Inició hace unos días
            'fecha_fin' => now()->addMonths(rand(1, 3)), // Termina en unos meses
            // Asumimos que el capacitador se asignará desde el Seeder
            'capacitador_id' => User::factory(),
        ];
    }
}