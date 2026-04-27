<?php

namespace Database\Seeders;

use Database\Seeders\Common\AdminUserSeeder;
use Database\Seeders\Common\EstamentoSeeder;
use Database\Seeders\Common\SedeSeeder;
use Database\Seeders\Testing\DemoCoursesSeeder;
use Database\Seeders\Testing\DemoProgressSeeder;
use Database\Seeders\Testing\DemoUsersSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SedeSeeder::class,
            EstamentoSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call([
                DemoCoursesSeeder::class,
                DemoUsersSeeder::class,
                DemoProgressSeeder::class,
            ]);
        }
    }
}
