<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sexo = fake()->randomElement(['M', 'F']);
        
        // Generación de RUT chileno válido
        $base = fake()->unique()->randomNumber(8, true);
        $sum = 0;
        $multiplier = 2;
        $digits = str_split(strrev($base));
        foreach ($digits as $digit) {
            $sum += $digit * $multiplier;
            $multiplier = $multiplier == 7 ? 2 : $multiplier + 1;
        }
        $digit = 11 - ($sum % 11);
        $dv = match($digit) {
            11 => '0',
            10 => 'K',
            default => (string)$digit,
        };
        $rut = number_format($base, 0, '', '.') . '-' . $dv;

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'rut' => $rut,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'sexo' => $sexo,
            // Rango amplio para probar el filtro etario (18 a 70 años aprox.)
            'fecha_nacimiento' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'activo' => fake()->boolean(85),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
