<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Modulo>
 */
class ModuloFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => 'Módulo: '.$this->faker->sentence(3),
            'curso_id' => Curso::factory(),
            'orden' => 1,
            'tipo_contenido' => $this->faker->randomElement(['video', 'pdf', 'ppt', 'texto', 'imagen']),
            'ruta_archivo' => null,
            'nombre_archivo_original' => null,
            'contenido' => null,
            'duracion_minutos' => $this->faker->numberBetween(5, 25),
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_contenido' => 'video',
            'ruta_archivo' => 'modulos/demo/video-seguridad.mp4',
            'nombre_archivo_original' => 'video-seguridad.mp4',
            'contenido' => null,
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_contenido' => 'pdf',
            'ruta_archivo' => 'modulos/demo/manual.pdf',
            'nombre_archivo_original' => 'manual.pdf',
            'contenido' => null,
        ]);
    }

    public function ppt(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_contenido' => 'ppt',
            'ruta_archivo' => 'modulos/demo/presentacion.pptx',
            'nombre_archivo_original' => 'presentacion.pptx',
            'contenido' => null,
        ]);
    }

    public function texto(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_contenido' => 'texto',
            'ruta_archivo' => null,
            'nombre_archivo_original' => null,
            'contenido' => $this->faker->paragraphs(3, true),
        ]);
    }

    public function imagen(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_contenido' => 'imagen',
            'ruta_archivo' => 'modulos/demo/infografia.webp',
            'nombre_archivo_original' => 'infografia.webp',
            'contenido' => null,
        ]);
    }

    public function evaluacion(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_contenido' => 'evaluacion',
            'ruta_archivo' => null,
            'nombre_archivo_original' => null,
            'contenido' => null,
            'duracion_minutos' => null,
        ]);
    }
}
