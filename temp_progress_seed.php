<?php

use App\Models\Curso;
use App\Models\ProgresoModulo;
use App\Models\User;

$cursos = Curso::with('modulos')->get();

if ($cursos->isEmpty()) {
    echo "No hay cursos disponibles para asignar progreso.\n";

    return;
}

$usuarios = User::inRandomOrder()->take(50)->get();
$counts = 0;

foreach ($cursos as $curso) {
    // Solo le asignamos progreso a la mitad de los usuarios para cada curso, para variedad.
    $usuariosCurso = $usuarios->random(min(20, $usuarios->count()));
    $modulos = $curso->modulos;

    if ($modulos->isEmpty()) {
        continue;
    }

    foreach ($usuariosCurso as $user) {
        $numCompletados = rand(0, $modulos->count());

        if ($numCompletados > 0) {
            $modulosCompletados = $modulos->random($numCompletados);

            foreach ($modulosCompletados as $modulo) {
                ProgresoModulo::updateOrCreate(
                    ['user_id' => $user->id, 'modulo_id' => $modulo->id],
                    ['completado' => true, 'fecha_completado' => now()]
                );
                $counts++;
            }
        }
    }
}

echo "Se agregaron {$counts} registros de progreso en total a la base de datos.\n";
