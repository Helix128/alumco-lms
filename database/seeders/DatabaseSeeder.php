<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sede;
use App\Models\Estamento;
use App\Models\User;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\Certificado;
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

        // 3. Crear 5 Cursos Clave
        $cursosNombres = ['Infecciones Intrahospitalarias', 'Manejo de Residuos (REAS)', 'RCP Básico', 'Ley de Derechos del Paciente', 'Higiene de Manos'];
        $cursos = collect();

        foreach ($cursosNombres as $nombre) {
            $cursos->push(Curso::create([
                'titulo' => $nombre,
                'descripcion' => 'Curso obligatorio de capacitación continua.',
                'fecha_inicio' => now()->subMonths(6),
                'fecha_fin' => now()->addMonths(6),
                'capacitador_id' => $admin->id, // El admin los imparte por ahora
            ]));
        }

        // 4. Crear 50 Trabajadores y darles Certificados (Cursos aprobados)
        User::factory(50)->create()->each(function ($user) use ($sedes, $estamentos, $cursos) {
            // Asignar sede y estamento aleatorio
            $user->update([
                'sede_id' => $sedes->random()->id,
                'estamento_id' => $estamentos->random()->id,
            ]);

            // Aprobar entre 1 y 3 cursos al azar para este usuario
            $cursosAprobados = $cursos->random(rand(1, 3));

            foreach ($cursosAprobados as $curso) {
                Certificado::create([
                    'user_id' => $user->id,
                    'curso_id' => $curso->id,
                    'codigo_verificacion' => Str::uuid(),
                    'ruta_pdf' => '/certificados/demo.pdf',
                    // Fecha aleatoria de aprobación en los últimos 365 días
                    'fecha_emision' => now()->subDays(rand(1, 365)),
                ]);
            }
        });
    }
}