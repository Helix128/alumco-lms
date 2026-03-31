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
        $estamento = Estamento::query()->first();

        if (! $sede || ! $estamento) {
            return;
        }

        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@Alumco.cl');
        $adminName = env('SEED_ADMIN_NAME', 'Admin Alumco');
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'password');

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'sede_id' => $sede->id,
                'estamento_id' => $estamento->id,
                'activo' => true,
            ]
        );
    }
}
