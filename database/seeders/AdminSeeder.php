<?php

namespace Database\Seeders;

use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $sedes = collect([
            Sede::firstOrCreate(['nombre' => 'Concepcion']),
            Sede::firstOrCreate(['nombre' => 'Hualpén']),
            Sede::firstOrCreate(['nombre' => 'Coyhaique']),
        ]);

        $estamentos = collect([
            Estamento::firstOrCreate(['nombre' => 'Profesionales']),
            Estamento::firstOrCreate(['nombre' => 'Auxiliares de servicio']),
            Estamento::firstOrCreate(['nombre' => 'Administracion']),
        ]);

        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@Alumco.cl');
        $adminName = env('SEED_ADMIN_NAME', 'Admin Alumco');
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'sede_id' => $sedes->first()->id,
                'estamento_id' => $estamentos->first()->id,
                'activo' => true,
            ]
        );
    }
}