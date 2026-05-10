<?php

namespace Database\Seeders\Common;

use App\Models\Estamento;
use Illuminate\Database\Seeder;

class EstamentoSeeder extends Seeder
{
    public function run(): void
    {
        $estamentos = [
            'Profesionales',
            'Auxiliares de servicio',
            'Manipuladores de alimentos',
            'Asistentes de trato directo (ATD)',
            'Personal de administración',
        ];

        foreach ($estamentos as $nombre) {
            Estamento::query()->firstOrCreate(['nombre' => $nombre]);
        }
    }
}
