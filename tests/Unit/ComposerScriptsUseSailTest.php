<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ComposerScriptsUseSailTest extends TestCase
{
    public function test_operational_composer_scripts_use_sail_wrappers(): void
    {
        $composer = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
        $scripts = implode("\n", [
            ...$composer['scripts']['setup'],
            ...$composer['scripts']['dev'],
            ...$composer['scripts']['test'],
        ]);

        $this->assertStringNotContainsString('@php artisan', $scripts);
        $this->assertStringNotContainsString('php artisan ', $scripts);
        $this->assertStringContainsString('./vendor/bin/sail artisan', $scripts);
        $this->assertStringContainsString('./vendor/bin/sail npm', $scripts);
    }
}
