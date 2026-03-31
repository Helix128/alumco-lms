<?php

namespace Database\Seeders\Testing;

use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $sedes = Sede::query()->get();
        $estamentos = Estamento::query()
            ->whereNotIn('nombre', ['Desarrollador', 'Administrador'])
            ->get();

        if ($sedes->isEmpty() || $estamentos->isEmpty()) {
            return;
        }

        User::factory(250)->create()->each(function (User $user) use ($sedes, $estamentos): void {
            $user->update([
                'sede_id' => $sedes->random()->id,
                'estamento_id' => $estamentos->random()->id,
            ]);
        });
    }
}
