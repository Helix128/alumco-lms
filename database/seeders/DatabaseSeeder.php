<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sede;
use App\Models\Estamento;
use App\Models\User;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\Certificado;
use App\Models\ProgresoModulo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Sedes y Estamentos
        $sedes = collect([
            Sede::create(['nombre' => 'Concepción']),
            Sede::create(['nombre' => 'Hualpén']),
            Sede::create(['nombre' => 'Coyhaique']),
        ]);

        $estamentos = collect([
            Estamento::create(['nombre' => 'Profesionales']),
            Estamento::create(['nombre' => 'Auxiliares de servicio']),
            Estamento::create(['nombre' => 'Administración']),
        ]);

        // 2. Crear Usuarios Base
        $admin = User::create([
            'name' => 'Admin Alunco',
            'email' => 'admin@alunco.cl',
            'password' => Hash::make('password'),
            'sede_id' => $sedes[0]->id,
            'estamento_id' => $estamentos[0]->id,
        ]);

        // 3. Crear Cursos y sus Módulos
        $cursosNombres = ['Infecciones Intrahospitalarias', 'Manejo de Residuos (REAS)', 'RCP Básico', 'Ley de Derechos del Paciente', 'Higiene de Manos'];
        $cursos = collect();

        foreach ($cursosNombres as $nombre) {
            $curso = Curso::create([
                'titulo' => $nombre,
                'descripcion' => 'Curso obligatorio de capacitación continua.',
                'fecha_inicio' => now()->subMonths(6),
                'fecha_fin' => now()->addMonths(6),
                'capacitador_id' => $admin->id,
            ]);
            $cursos->push($curso);

            // Le creamos 4 módulos a cada curso
            for ($i = 1; $i <= 4; $i++) {
                Modulo::create([
                    'curso_id' => $curso->id,
                    'titulo' => "Módulo $i: Contenido del curso",
                    'orden' => $i,
                    'tipo_contenido' => 'video',
                ]);
            }
        }

        // 4. Crear 50 Trabajadores y simular su progreso
        User::factory(50)->create()->each(function ($user) use ($sedes, $estamentos, $cursos) {

            $user->update([
                'sede_id' => $sedes->random()->id,
                'estamento_id' => $estamentos->random()->id,
            ]);

            // El usuario interactúa con 1 a 3 cursos al azar
            $cursosInteractuados = $cursos->random(rand(1, 3));

            foreach ($cursosInteractuados as $curso) {
                // Obtenemos los 4 módulos de este curso
                $modulos = Modulo::where('curso_id', $curso->id)->orderBy('orden')->get();

                // Decidimos al azar si el usuario completó el curso (1) o lo dejó a medias (0)
                $loTermino = rand(0, 1);

                if ($loTermino === 1) {
                    // COMPLETÓ EL CURSO: Llenamos el progreso de los 4 módulos
                    foreach ($modulos as $modulo) {
                        ProgresoModulo::create([
                            'user_id' => $user->id,
                            'modulo_id' => $modulo->id,
                            'completado' => true,
                            'fecha_completado' => now()->subDays(rand(1, 30))
                        ]);
                    }

                    // Y le damos su certificado
                    Certificado::create([
                        'user_id' => $user->id,
                        'curso_id' => $curso->id,
                        'codigo_verificacion' => Str::uuid(),
                        'ruta_pdf' => '/certificados/demo.pdf',
                        'fecha_emision' => now()->subDays(rand(1, 30)),
                    ]);

                } else {
                    // CURSO A MEDIAS: Completa 1, 2 o 3 módulos solamente
                    $modulosCompletados = rand(1, 3);

                    for ($i = 0; $i < $modulosCompletados; $i++) {
                        ProgresoModulo::create([
                            'user_id' => $user->id,
                            'modulo_id' => $modulos[$i]->id,
                            'completado' => true,
                            'fecha_completado' => now()->subDays(rand(1, 10))
                        ]);
                    }
                }
            }
        });
    }
}