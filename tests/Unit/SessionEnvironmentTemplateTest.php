<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SessionEnvironmentTemplateTest extends TestCase
{
    public function test_session_defaults_are_encrypted_and_secure(): void
    {
        $variables = [];

        foreach (file(dirname(__DIR__, 2).'/.env.example', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (! str_contains($line, '=') || str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $variables[$key] = trim($value, "\"'");
        }

        $this->assertSame('database', $variables['SESSION_DRIVER'] ?? null);
        $this->assertSame('true', $variables['SESSION_ENCRYPT'] ?? null);
        $this->assertSame('true', $variables['SESSION_SECURE_COOKIE'] ?? null);
        $this->assertSame('lax', $variables['SESSION_SAME_SITE'] ?? null);
    }
}
