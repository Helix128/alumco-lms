<?php

namespace Database\Seeders\Testing;

use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    private const USER_COUNT = 10;

    public function run(): void
    {
        $sedes = Sede::query()->get();
        $estamentos = Estamento::query()
            ->whereNotIn('nombre', ['Desarrollador', 'Administrador'])
            ->orderBy('id')
            ->get();

        if ($sedes->isEmpty() || $estamentos->isEmpty()) {
            return;
        }

        $demoEmails = [];

        for ($i = 1; $i <= self::USER_COUNT; $i++) {
            $email = sprintf('trabajador.demo.%03d@alumco.local', $i);
            $demoEmails[] = $email;
            $attributes = [
                'email' => $email,
                'name' => sprintf('Trabajador Demo %03d', $i),
                'rut' => $this->rutFor(23000000 + $i),
                'password' => Hash::make('password'),
                'sexo' => $i % 2 === 0 ? 'F' : 'M',
                'fecha_nacimiento' => now()->subYears(22 + ($i % 35))->toDateString(),
                'sede_id' => $sedes[($i - 1) % $sedes->count()]->id,
                'estamento_id' => $estamentos[($i - 1) % $estamentos->count()]->id,
                'activo' => $i % 10 !== 0,
            ];

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                $attributes
            );

            $user->assignRole('Trabajador');
        }

        User::withTrashed()
            ->where('email', 'like', 'trabajador.demo.%@alumco.local')
            ->whereNotIn('email', $demoEmails)
            ->get()
            ->each(fn (User $user) => $user->forceDelete());
    }

    private function rutFor(int $base): string
    {
        $sum = 0;
        $multiplier = 2;

        foreach (str_split(strrev((string) $base)) as $digit) {
            $sum += ((int) $digit) * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $digit = 11 - ($sum % 11);
        $verifier = match ($digit) {
            11 => '0',
            10 => 'K',
            default => (string) $digit,
        };

        return number_format($base, 0, '', '.').'-'.$verifier;
    }
}
