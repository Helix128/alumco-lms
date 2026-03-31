<?php

namespace Database\Seeders\Common;

use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $sede = Sede::query()->first();
        $estamentoDev = Estamento::where('nombre', 'Desarrollador')->first();
        $estamentoAdmin = Estamento::where('nombre', 'Administrador')->first();

        if (! $sede || ! $estamentoDev || ! $estamentoAdmin) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'dev@alumco.cl'],
            [
                'name' => 'Dev Alumco',
                'password' => Hash::make('password'),
                'sede_id' => $sede->id,
                'estamento_id' => $estamentoDev->id,
                'activo' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@alumco.cl'],
            [
                'name' => 'Admin Alumco',
                'password' => Hash::make('password'),
                'sede_id' => $sede->id,
                'estamento_id' => $estamentoAdmin->id,
                'activo' => true,
            ]
        );
    }
}
