<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asegurarnos de que los roles existan (por si acaso no se corrió el seeder)
        $roles = [
            'Desarrollador',
            'Administrador',
            'Capacitador Interno',
            'Capacitador Externo',
            'Trabajador',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Obtener todos los usuarios con sus estamentos
        $users = User::with('estamento')->get();

        foreach ($users as $user) {
            if (! $user->estamento) {
                $user->assignRole('Trabajador');

                continue;
            }

            switch ($user->estamento->nombre) {
                case 'Desarrollador':
                    $user->assignRole('Desarrollador');
                    break;
                case 'Administrador':
                    $user->assignRole('Administrador');
                    break;
                case 'Capacitador Interno':
                    $user->assignRole('Capacitador Interno');
                    break;
                case 'Capacitador Externo':
                    $user->assignRole('Capacitador Externo');
                    break;
                default:
                    $user->assignRole('Trabajador');
                    break;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar todas las asignaciones de roles
        foreach (User::all() as $user) {
            $user->roles()->detach();
        }
    }
};
