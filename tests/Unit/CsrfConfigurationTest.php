<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CsrfConfigurationTest extends TestCase
{
    public function test_application_does_not_define_global_csrf_exceptions(): void
    {
        $csrfMiddleware = dirname(__DIR__, 2).'/app/Http/Middleware/VerifyCsrfToken.php';

        $this->assertFileDoesNotExist($csrfMiddleware);
        $this->assertStringNotContainsString(
            'validateCsrfTokens(except:',
            file_get_contents(dirname(__DIR__, 2).'/bootstrap/app.php')
        );
    }
}
