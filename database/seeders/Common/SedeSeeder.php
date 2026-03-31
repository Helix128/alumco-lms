<?php

namespace Database\Seeders\Common;

use App\Models\Sede;
use Illuminate\Database\Seeder;

class SedeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Concepcion', 'Hualpen', 'Coyhaique'] as $nombre) {
            Sede::query()->firstOrCreate(['nombre' => $nombre]);
        }
    }
}
