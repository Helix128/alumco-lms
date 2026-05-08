<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ComposerRuntimeTest extends TestCase
{
    public function test_composer_requires_the_container_php_runtime(): void
    {
        $composer = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('^8.4', $composer['require']['php'] ?? null);
    }
}
