<?php

namespace Tests\Unit;

use App\Support\Reports\ReportFilters;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_report_filters_normalize_ids_from_mixed_input(): void
    {
        $filters = ReportFilters::fromValidatedInput([
            'sede_id' => ['2', '2', '-1', '0', 'Santiago'],
            'estamento_id' => '4',
            'curso_id' => [9, null, 9],
        ]);

        $this->assertSame([2], $filters->sedeIds);
        $this->assertSame([4], $filters->estamentoIds);
        $this->assertSame([9], $filters->courseIds);
    }
}
