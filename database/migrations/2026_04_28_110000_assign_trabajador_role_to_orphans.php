<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asignamos el rol 'Trabajador' a los usuarios que no tengan ningún rol asignado
        User::whereDoesntHave('roles')->get()->each(function (User $user) {
            $user->assignRole('Trabajador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos cambios de roles masivos para evitar inconsistencias
    }
};
