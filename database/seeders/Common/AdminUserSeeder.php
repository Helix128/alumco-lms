<?php

namespace Database\Seeders\Common;

use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $sede = Sede::query()->first();

        if (! $sede) {
            return;
        }

        $dev = User::query()->updateOrCreate(
            ['email' => 'dev@alumco.cl'],
            [
                'name' => 'Dev Alumco',
                'password' => Hash::make('password'),
                'rut' => '11.111.111-1',
                'sede_id' => $sede->id,
                'estamento_id' => null,
                'activo' => true,
            ]
        );
        $dev->assignRole('Desarrollador');

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@alumco.cl'],
            [
                'name' => 'Admin Alumco',
                'password' => Hash::make('password'),
                'rut' => '22.222.222-2',
                'sede_id' => $sede->id,
                'estamento_id' => null,
                'activo' => true,
            ]
        );
        $admin->assignRole('Administrador');
    }
}
