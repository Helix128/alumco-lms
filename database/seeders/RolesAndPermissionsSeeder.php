<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de roles y permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear Roles
        Role::firstOrCreate(['name' => 'Desarrollador']);
        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'Capacitador Interno']);
        Role::firstOrCreate(['name' => 'Capacitador Externo']);
        Role::firstOrCreate(['name' => 'Trabajador']);

        // Aquí podríamos crear permisos específicos en el futuro
        // Permission::create(['name' => 'editar cursos']);
    }
}
