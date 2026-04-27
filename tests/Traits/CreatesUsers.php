<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;

trait CreatesUsers
{
    protected function createDev(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Desarrollador');
        return $user;
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');
        return $user;
    }

    protected function createTrabajador(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Trabajador');
        return $user;
    }
}
