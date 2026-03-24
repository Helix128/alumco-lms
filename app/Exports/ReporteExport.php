<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReporteExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    private array $selectedSedeIds;

    private array $selectedEstamentoIds;

    private array $selectedCourseIds;

    private ?int $edadMin;

    private ?int $edadMax;

    // Recibimos los filtros de la vista a través del Request
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->selectedSedeIds = $this->sanitizeSedeIds($request);
        $this->selectedEstamentoIds = $this->sanitizeEstamentoIds($request);
        $this->selectedCourseIds = $this->sanitizeCourseIds($request);
        [$this->edadMin, $this->edadMax] = $this->sanitizeAgeRange($request);
    }

    private function sanitizeSedeIds(Request $request): array
    {
        $rawIds = $request->input('sede_id', []);
        if (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        $ids = array_map('intval', $rawIds);
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        return $ids;
    }

    private function sanitizeEstamentoIds(Request $request): array
    {
        $rawIds = $request->input('estamento_id', []);
        if (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        $ids = array_map('intval', $rawIds);
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        return $ids;
    }

    private function sanitizeCourseIds(Request $request): array
    {
        $rawIds = $request->input('curso_id', []);
        if (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        $ids = array_map('intval', $rawIds);
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        return $ids;
    }

    private function sanitizeAgeRange(Request $request): array
    {
        $edadMin = $request->input('edad_min');
        $edadMax = $request->input('edad_max');

        $edadMin = is_numeric($edadMin) ? max((int) $edadMin, 0) : null;
        $edadMax = is_numeric($edadMax) ? max((int) $edadMax, 0) : null;

        if ($edadMin !== null && $edadMax !== null && $edadMin > $edadMax) {
            [$edadMin, $edadMax] = [$edadMax, $edadMin];
        }

        return [$edadMin, $edadMax];
    }

    public function query()
    {
        // Esta es exactamente la misma consulta que tienes en tu ReporteController
        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        if ($this->edadMin !== null || $this->edadMax !== null) {
            $query->whereNotNull('fecha_nacimiento');

            if ($this->edadMin !== null) {
                $query->where('fecha_nacimiento', '<=', now()->subYears($this->edadMin)->toDateString());
            }

            if ($this->edadMax !== null) {
                $query->where('fecha_nacimiento', '>=', now()->subYears($this->edadMax + 1)->addDay()->toDateString());
            }
        }

        if (!empty($this->selectedEstamentoIds)) {
            $query->whereIn('estamento_id', $this->selectedEstamentoIds);
        }

        if (!empty($this->selectedSedeIds)) {
            $query->whereIn('sede_id', $this->selectedSedeIds);
        }

        if (!empty($this->selectedCourseIds) || ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin'))) {
            $query->whereHas('certificados', function ($q) {
                if (!empty($this->selectedCourseIds)) {
                    // AND logic: the user must have every selected course.
                    $q->whereIn('curso_id', $this->selectedCourseIds)
                        ->select('user_id')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(DISTINCT curso_id) = ?', [count($this->selectedCourseIds)]);
                }
                if ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin')) {
                    $q->whereBetween('fecha_emision', [$this->request->fecha_inicio, $this->request->fecha_fin]);
                }
            });
        }

        return $query;
    }

    // Definimos los títulos de las columnas en el Excel
    public function headings(): array
    {
        return [
            'Nombre',
            'Sexo',
            'Edad',
            'Correo',
            'Sede',
            'Estamento',
            'Cursos Aprobados (Fecha)'
        ];
    }

    // Mapeamos los datos de cada usuario a una fila del Excel
    public function map($user): array
    {
        // Extraemos todos los cursos aprobados y los unimos en un solo texto separado por comas
        $cursosText = $user->certificados->map(function ($certificado) {
            return $certificado->curso->titulo . ' (' . $certificado->fecha_emision->format('d/m/Y') . ')';
        })->implode(', ');

        $sexo = strtolower((string) ($user->sexo ?? ''));
        if ($sexo === 'm' || $sexo === 'masculino' || $sexo === 'hombre') {
            $sexoLabel = 'Masculino';
        } elseif ($sexo === 'f' || $sexo === 'femenino' || $sexo === 'mujer') {
            $sexoLabel = 'Femenino';
        } elseif ($sexo !== '') {
            $sexoLabel = ucfirst($sexo);
        } else {
            $sexoLabel = 'No informado';
        }

        $edadNacimiento = 'Sin fecha';
        if ($user->fecha_nacimiento) {
            $nacimiento = Carbon::parse($user->fecha_nacimiento);
            $edadNacimiento = $nacimiento->age;
        }

        return [
            $user->name,
            $sexoLabel,
            $edadNacimiento,
            $user->email,
            $user->sede->nombre ?? 'N/A',
            $user->estamento->nombre ?? 'N/A',
            $cursosText ?: 'Sin cursos aprobados'
        ];
    }
}