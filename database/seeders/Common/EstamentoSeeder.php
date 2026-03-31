<?php

namespace Database\Seeders\Common;

use App\Models\Estamento;
use Illuminate\Database\Seeder;

class EstamentoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Desarrollador', 'Administrador', 'Profesionales', 'Auxiliares de servicio'] as $nombre) {
            Estamento::query()->firstOrCreate(['nombre' => $nombre]);
        }
    }
}
