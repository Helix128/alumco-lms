<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EnvironmentTemplateSecurityTest extends TestCase
{
    public function test_environment_template_uses_safe_production_defaults(): void
    {
        $variables = $this->environmentVariables();

        $this->assertSame('Alumco', $variables['APP_NAME'] ?? null);
        $this->assertSame('production', $variables['APP_ENV'] ?? null);
        $this->assertSame('false', $variables['APP_DEBUG'] ?? null);
        $this->assertSame('https://lms.alumco.cl', $variables['APP_URL'] ?? null);
        $this->assertSame('warning', $variables['LOG_LEVEL'] ?? null);
        $this->assertSame('log', $variables['MAIL_MAILER'] ?? null);
        $this->assertNotSame('smtp.zoho.com', $variables['MAIL_HOST'] ?? null);
        $this->assertSame('true', $variables['SESSION_SECURE_COOKIE'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    private function environmentVariables(): array
    {
        $path = dirname(__DIR__, 2).'/.env.example';
        $variables = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $variables[$key] = trim($value, "\"'");
        }

        return $variables;
    }
}
