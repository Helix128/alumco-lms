<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SessionEnvironmentTemplateTest extends TestCase
{
    public function test_application_defaults_to_localhost(): void
    {
        $variables = $this->environmentTemplateVariables();

        $this->assertSame('http://localhost', $variables['APP_URL'] ?? null);
        $this->assertSame('80', $variables['APP_PORT'] ?? null);
        $this->assertSame('5173', $variables['VITE_PORT'] ?? null);
    }

    public function test_session_defaults_are_encrypted_and_http_local_compatible(): void
    {
        $variables = $this->environmentTemplateVariables();

        $this->assertSame('database', $variables['SESSION_DRIVER'] ?? null);
        $this->assertSame('true', $variables['SESSION_ENCRYPT'] ?? null);
        $this->assertSame('false', $variables['SESSION_SECURE_COOKIE'] ?? null);
        $this->assertSame('true', $variables['SESSION_HTTP_ONLY'] ?? null);
        $this->assertSame('lax', $variables['SESSION_SAME_SITE'] ?? null);
    }

    public function test_cache_and_queue_default_to_redis(): void
    {
        $variables = $this->environmentTemplateVariables();

        $this->assertSame('redis', $variables['CACHE_STORE'] ?? null);
        $this->assertSame('redis', $variables['QUEUE_CONNECTION'] ?? null);
        $this->assertSame('phpredis', $variables['REDIS_CLIENT'] ?? null);
        $this->assertSame('redis', $variables['REDIS_HOST'] ?? null);
        $this->assertSame('0', $variables['REDIS_DB'] ?? null);
        $this->assertSame('1', $variables['REDIS_CACHE_DB'] ?? null);
        $this->assertSame('default', $variables['REDIS_QUEUE_CONNECTION'] ?? null);
    }

    public function test_filesystem_defaults_to_local_without_aws_environment_variables(): void
    {
        $variables = $this->environmentTemplateVariables();

        $this->assertSame('local', $variables['FILESYSTEM_DISK'] ?? null);
        $this->assertArrayNotHasKey('AWS_ACCESS_KEY_ID', $variables);
        $this->assertArrayNotHasKey('AWS_SECRET_ACCESS_KEY', $variables);
        $this->assertArrayNotHasKey('AWS_DEFAULT_REGION', $variables);
        $this->assertArrayNotHasKey('AWS_BUCKET', $variables);
        $this->assertArrayNotHasKey('AWS_USE_PATH_STYLE_ENDPOINT', $variables);
    }

    public function test_sail_user_defaults_match_local_permissions(): void
    {
        $variables = $this->environmentTemplateVariables();

        $this->assertSame('1000', $variables['WWWUSER'] ?? null);
        $this->assertSame('1000', $variables['WWWGROUP'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    private function environmentTemplateVariables(): array
    {
        $variables = [];

        foreach (file(dirname(__DIR__, 2).'/.env.example', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (! str_contains($line, '=') || str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $variables[$key] = trim($value, "\"'");
        }

        return $variables;
    }
}
