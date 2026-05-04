<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageRenderingTest extends TestCase
{
    public function test_maintenance_error_page_renders_without_exception_context(): void
    {
        $contents = view('errors.503')->render();

        $this->assertStringContainsString(
            'El sitio se encuentra temporalmente en mantenimiento.',
            $contents,
        );
    }
}
