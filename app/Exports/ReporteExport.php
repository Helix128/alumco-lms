<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $request;

    private array $selectedSedeIds;

    private array $selectedEstamentoIds;

    private array $selectedCourseIds;

    private ?int $edadMin;

    private ?int $edadMax;

    private array $columnasSeleccionadas;

    private array $nombresPersonalizados;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->selectedSedeIds = $this->sanitizeSedeIds($request);
        $this->selectedEstamentoIds = $this->sanitizeEstamentoIds($request);
        $this->selectedCourseIds = $this->sanitizeCourseIds($request);
        [$this->edadMin, $this->edadMax] = $this->sanitizeAgeRange($request);

        // Claves de columnas habilitadas
        $this->columnasSeleccionadas = $request->input('columnas', [
            'rut', 'nombre', 'sexo', 'edad', 'email', 'sede', 'estamento', 'cursos',
        ]);

        // Arreglo de nombres personalizados (ej: ['nombre' => 'Nombre del Trabajador'])
        $this->nombresPersonalizados = $request->input('nombres', [
            'rut' => 'RUT',
            'nombre' => 'Nombre completo',
            'sexo' => 'Sexo / Género',
            'edad' => 'Edad actual',
            'email' => 'Correo electrónico',
            'sede' => 'Sede asignada',
            'estamento' => 'Estamento / Rol',
            'cursos' => 'Cursos aprobados',
        ]);
    }

    private function sanitizeSedeIds(Request $request): array
    {
        $rawIds = $request->input('sede_id', []);
        if (! is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        return array_values(array_unique(array_filter(array_map('intval', $rawIds), fn ($id) => $id > 0)));
    }

    private function sanitizeEstamentoIds(Request $request): array
    {
        $rawIds = $request->input('estamento_id', []);
        if (! is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        return array_values(array_unique(array_filter(array_map('intval', $rawIds), fn ($id) => $id > 0)));
    }

    private function sanitizeCourseIds(Request $request): array
    {
        $rawIds = $request->input('curso_id', []);
        if (! is_array($rawIds)) {
            $rawIds = [$rawIds];
        }

        return array_values(array_unique(array_filter(array_map('intval', $rawIds), fn ($id) => $id > 0)));
    }

    private function sanitizeAgeRange(Request $request): array
    {
        $edadMin = is_numeric($request->input('edad_min')) ? max((int) $request->input('edad_min'), 0) : null;
        $edadMax = is_numeric($request->input('edad_max')) ? max((int) $request->input('edad_max'), 0) : null;
        if ($edadMin !== null && $edadMax !== null && $edadMin > $edadMax) {
            [$edadMin, $edadMax] = [$edadMax, $edadMin];
        }

        return [$edadMin, $edadMax];
    }

    public function query()
    {
        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        if ($this->edadMin !== null) {
            $query->where('fecha_nacimiento', '<=', Carbon::now()->subYears($this->edadMin)->format('Y-m-d'));
        }
        if ($this->edadMax !== null) {
            $query->where('fecha_nacimiento', '>=', Carbon::now()->subYears($this->edadMax + 1)->addDay()->format('Y-m-d'));
        }

        if (! empty($this->selectedEstamentoIds)) {
            $query->whereIn('estamento_id', $this->selectedEstamentoIds);
        }
        if (! empty($this->selectedSedeIds)) {
            $query->whereIn('sede_id', $this->selectedSedeIds);
        }

        if (! empty($this->selectedCourseIds) || ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin'))) {
            $query->whereHas('certificados', function ($q) {
                if (! empty($this->selectedCourseIds)) {
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
                    $rowData[] = $user->certificados->map(fn ($c) => $c->curso->titulo.' ('.$c->fecha_emision->format('d/m/Y').')')->implode(', ') ?: 'Sin cursos aprobados';
                    break;
            }
        }

        return $rowData;
    }
}
