<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_is_redirected_to_login_from_course_catalog(): void
    {
        $this->get(route('cursos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_login_password_field_is_masked_by_default(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="password"',
                'type="password"',
                'x-bind:type="showPassword ? \'text\' : \'password\'"',
                'name="password"',
            ], false);
    }
}
