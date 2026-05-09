<?php

namespace Tests\Unit;

use App\Exports\ReporteExport;
use App\Models\User;
use App\Services\Reports\AdminTrainingReportQuery;
use App\Support\Reports\ReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ReporteExportTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_export_query_uses_admin_training_report_query_service(): void
    {
        $request = Request::create('/admin/reportes/exportar', 'GET', [
            'sede_id' => ['1'],
            'estamento_id' => ['2'],
            'curso_id' => ['3'],
            'edad_min' => '20',
            'edad_max' => '65',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'estado_capacitacion' => 'certificado',
        ]);

        $service = Mockery::mock(AdminTrainingReportQuery::class);
        $service->shouldReceive('ageBounds')
            ->once()
            ->andReturn(['min' => 18, 'max' => 80]);
        $service->shouldReceive('selectedCourse')
            ->once()
            ->with(Mockery::on(fn (ReportFilters $filters): bool => $filters->courseIds === [3]))
            ->andReturn(null);
        $service->shouldReceive('participants')
            ->once()
            ->with(
                Mockery::type(ReportFilters::class),
                null,
                ['min' => 18, 'max' => 80],
            )
            ->andReturn(User::query());

        $this->app->instance(AdminTrainingReportQuery::class, $service);

        $query = (new ReporteExport($request))->query();

        $this->assertInstanceOf(Builder::class, $query);
    }
}
