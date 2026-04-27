<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Garantizar que los roles existan
        $seeder = new RolesAndPermissionsSeeder();
        $seeder->run();

        // 2. Fix para usuarios administradores y desarrolladores por email (seeders antiguos)
        $usersToFix = [
            'dev@alumco.cl' => 'Desarrollador',
            'admin@alumco.cl' => 'Administrador',
        ];

        foreach ($usersToFix as $email => $roleName) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->assignRole($roleName);
            }
        }

        // 3. Fix para cualquier usuario que tenga estamento de sistema (legado)
        // Buscamos estamentos con nombres de roles
        $usersWithSystemEstamento = User::whereHas('estamento', function($q) {
            $q->whereIn('nombre', ['Desarrollador', 'Administrador', 'Capacitador Interno', 'Capacitador Externo']);
        })->with('estamento')->get();

        foreach ($usersWithSystemEstamento as $user) {
            $roleName = $user->estamento->nombre;
            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }
            
            // Opcional: Limpiar el estamento_id si coincide con el rol de sistema
            // $user->update(['estamento_id' => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir esto ya que son asignaciones de datos
    }
};
