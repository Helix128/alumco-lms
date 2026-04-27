<?php

namespace Database\Seeders\Common;

use App\Models\Estamento;
use Illuminate\Database\Seeder;

class EstamentoSeeder extends Seeder
{
    public function run(): void
    {
        $estamentos = [
            'Directivos',
            'Profesionales',
            'Técnicos',
            'Administrativos',
            'Operarios',
            'Auxiliares de servicio'
        ];

        foreach ($estamentos as $nombre) {
            Estamento::query()->firstOrCreate(['nombre' => $nombre]);
        }
    }
}
