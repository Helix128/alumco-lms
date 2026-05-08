<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductionEnvironmentTemplateTest extends TestCase
{
    public function test_environment_template_is_ready_for_production_urls_and_logging(): void
    {
        $variables = $this->environmentVariables();

        $this->assertSame('production', $variables['APP_ENV'] ?? null);
        $this->assertSame('false', $variables['APP_DEBUG'] ?? null);
        $this->assertSame('https://lms.alumco.cl', $variables['APP_URL'] ?? null);
        $this->assertSame('warning', $variables['LOG_LEVEL'] ?? null);
        $this->assertNotSame('http://localhost', $variables['APP_URL'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    private function environmentVariables(): array
    {
        $variables = [];

        foreach (file(dirname(__DIR__, 2).'/.env.example', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $variables[$key] = trim($value, "\"'");
        }

        return $variables;
    }
}
