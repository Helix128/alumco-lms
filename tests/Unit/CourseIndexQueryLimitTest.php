<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CourseIndexQueryLimitTest extends TestCase
{
    public function test_worker_course_index_limits_loaded_courses(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/CursoController.php');

        $this->assertStringContainsString('->limit(60)', $source);
    }
}
