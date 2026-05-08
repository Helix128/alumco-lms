<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VerEvaluacionExceptionReportingTest extends TestCase
{
    public function test_certificate_generation_exception_is_reported(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Livewire/VerEvaluacion.php');

        $this->assertStringContainsString('catch (\\Throwable $exception)', $source);
        $this->assertStringContainsString('report($exception);', $source);
    }
}
