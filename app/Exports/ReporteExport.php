<?php

namespace App\Exports;

use App\Models\Curso;
use App\Models\Feedback;
use App\Models\User;
use App\Services\Reports\AdminTrainingReportQuery;
use App\Support\Reports\ReportFilters;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    private ReportFilters $reportFilters;

    /**
     * @var array{min: int, max: int}
     */
    private array $ageBounds;

    private array $columnasSeleccionadas;

    private array $nombresPersonalizados;

    private ?Curso $cursoSeleccionado;

    public function __construct(Request $request)
    {
        $this->reportFilters = ReportFilters::fromValidatedInput($request->only([
            'sede_id',
            'estamento_id',
            'curso_id',
            'edad_min',
            'edad_max',
            'fecha_inicio',
            'fecha_fin',
            'estado_capacitacion',
        ]));
        $trainingReportQuery = app(AdminTrainingReportQuery::class);
        $this->ageBounds = $trainingReportQuery->ageBounds();
        $this->cursoSeleccionado = $trainingReportQuery->selectedCourse($this->reportFilters);

        // Claves de columnas habilitadas
        $this->columnasSeleccionadas = $request->input('columnas', [
            'rut', 'nombre', 'sexo', 'edad', 'email', 'sede', 'estamento', 'cursos', 'estado_capacitacion', 'progreso', 'feedback',
        ]);

        // Arreglo de nombres personalizados (ej: ['nombre' => 'Nombre del Colaborador'])
        $this->nombresPersonalizados = $request->input('nombres', [
            'rut' => 'RUT',
            'nombre' => 'Nombre completo',
            'sexo' => 'Sexo / Género',
            'edad' => 'Edad actual',
            'email' => 'Correo electrónico',
            'sede' => 'Sede asignada',
            'estamento' => 'Estamento / Rol',
            'cursos' => 'Capacitaciones aprobadas',
            'estado_capacitacion' => 'Estado capacitación',
            'progreso' => 'Progreso (%)',
            'feedback' => 'Feedback capacitación',
        ]);
    }

    public function query()
    {
        return app(AdminTrainingReportQuery::class)->participants(
            $this->reportFilters,
            $this->cursoSeleccionado,
            $this->ageBounds
        );
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->columnasSeleccionadas as $key) {
            $headers[] = $this->nombresPersonalizados[$key] ?? $key;
        }

        return $headers;
    }

    public function map($user): array
    {
        $rowData = [];

        foreach ($this->columnasSeleccionadas as $key) {
            switch ($key) {
                case 'rut':
                    $rowData[] = $user->rut ?? 'Sin RUT';
                    break;
                case 'nombre':
                    $rowData[] = $user->name;
                    break;
                case 'sexo':
                    $sexo = strtolower((string) ($user->sexo ?? ''));
                    $rowData[] = ($sexo === 'm' || $sexo === 'masculino' || $sexo === 'hombre') ? 'Masculino' : (($sexo === 'f' || $sexo === 'femenino' || $sexo === 'mujer') ? 'Femenino' : ($sexo !== '' ? ucfirst($sexo) : 'No informado'));
                    break;
                case 'edad':
                    $rowData[] = $user->fecha_nacimiento ? Carbon::parse($user->fecha_nacimiento)->age : 'Sin fecha';
                    break;
                case 'email':
                    $rowData[] = $user->email;
                    break;
                case 'sede':
                    $rowData[] = $user->sede->nombre ?? 'N/A';
                    break;
                case 'estamento':
                    $rowData[] = $user->estamento->nombre ?? 'N/A';
                    break;
                case 'cursos':
                    $rowData[] = $user->certificados->map(fn ($c) => $c->curso->titulo.' ('.$c->fecha_emision->format('d/m/Y').')')->implode(', ') ?: 'Sin capacitaciones aprobadas';
                    break;
                case 'estado_capacitacion':
                    $rowData[] = $this->courseStatusFor($user);
                    break;
                case 'progreso':
                    $rowData[] = $this->progressFor($user);
                    break;
                case 'feedback':
                    $rowData[] = $this->feedbackFor($user);
                    break;
            }
        }

        return $rowData;
    }

    private function progressFor(User $user): string
    {
        if (! $this->cursoSeleccionado || $this->cursoSeleccionado->modulos_count === 0) {
            return 'N/A';
        }

        $completed = $user->progresos()
            ->whereHas('modulo', fn ($query) => $query->where('curso_id', $this->cursoSeleccionado->id))
            ->where('completado', true)
            ->count();

        return (string) (int) round(($completed / $this->cursoSeleccionado->modulos_count) * 100);
    }

    private function courseStatusFor(User $user): string
    {
        if (! $this->cursoSeleccionado) {
            return 'N/A';
        }

        if ($user->certificados->where('curso_id', $this->cursoSeleccionado->id)->isNotEmpty()) {
            return 'Certificado';
        }

        $progress = (int) $this->progressFor($user);

        return match (true) {
            $progress === 0 => 'No iniciado',
            $progress >= 100 => 'Completado',
            default => 'En progreso',
        };
    }

    private function feedbackFor(User $user): string
    {
        if (! $this->cursoSeleccionado) {
            return 'N/A';
        }

        $feedback = Feedback::query()
            ->where('user_id', $user->id)
            ->where('curso_id', $this->cursoSeleccionado->id)
            ->where('tipo', Feedback::TipoCurso)
            ->first();

        if (! $feedback) {
            return 'Sin feedback';
        }

        return trim(($feedback->rating ? "{$feedback->rating}/5" : 'Sin nota').' '.$feedback->categoria.' '.($feedback->mensaje ?? ''));
    }
}
