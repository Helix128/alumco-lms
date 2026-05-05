<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('alumco:repair-system-roles')]
#[Description('Asigna roles de sistema (Desarrollador, Administrador) a cuentas que los necesiten. Idempotente.')]
class RepairSystemRoles extends Command
{
    private const SYSTEM_ACCOUNTS = [
        'dev@alumco.cl' => 'Desarrollador',
        'admin@alumco.cl' => 'Administrador',
    ];

    public function handle(): int
    {
        foreach (self::SYSTEM_ACCOUNTS as $email => $role) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->warn("Usuario {$email} no encontrado.");

                continue;
            }

            if ($user->hasRole($role)) {
                $this->line("✓ {$email} ya tiene el rol {$role}.");

                continue;
            }

            $user->assignRole($role);
            $this->info("✓ Rol {$role} asignado a {$email}.");
        }

        return self::SUCCESS;
    }
}
