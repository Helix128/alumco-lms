<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ModuloFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => 'Módulo: ' . $this->faker->sentence(3),
            'tipo_contenido' => $this->faker->randomElement(['video', 'pdf', 'ppt']),
            'ruta_archivo' => '/archivos/demo_' . $this->faker->word() . '.pdf',
            // El curso_id y el orden se inyectarán desde el Seeder
        ];
    }
}