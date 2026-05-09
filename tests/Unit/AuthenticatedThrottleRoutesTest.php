<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthenticatedThrottleRoutesTest extends TestCase
{
    public function test_authenticated_routes_have_general_throttle_middleware(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString("Route::middleware(['auth', 'throttle:120,1'])->group", $routes);
    }
}
