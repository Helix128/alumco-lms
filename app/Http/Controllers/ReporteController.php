<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Http\Requests\ReportFilterRequest;
use App\Services\Reports\AdminTrainingReportQuery;
use App\Support\Reports\ReportFilters;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function index(ReportFilterRequest $request, AdminTrainingReportQuery $trainingReportQuery): View
    {
        $reportFilters = ReportFilters::fromValidatedInput($request->validated());
        $ageBounds = $trainingReportQuery->ageBounds();
        $cursoSeleccionado = $trainingReportQuery->selectedCourse($reportFilters);
        $usuarios = $trainingReportQuery
            ->participants($reportFilters, $cursoSeleccionado, $ageBounds)
            ->paginate(15)
            ->withQueryString();

        return view('admin.reportes.index', [
            'usuarios' => $usuarios,
            'cursoSeleccionado' => $cursoSeleccionado,
            'ageBounds' => $ageBounds,
            ...$trainingReportQuery->catalogs(),
            ...$trainingReportQuery->selectedState($reportFilters),
        ]);
    }

    public function exportar(ReportFilterRequest $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}
