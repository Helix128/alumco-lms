<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReporteExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    // Recibimos los filtros de la vista a través del Request
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        // Esta es exactamente la misma consulta que tienes en tu ReporteController
        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        $query->when($this->request->filled('estamento_id'), function ($q) {
            $q->where('estamento_id', $this->request->estamento_id);
        });

        if ($this->request->filled('curso_id') || ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin'))) {
            $query->whereHas('certificados', function ($q) {
                if ($this->request->filled('curso_id')) {
                    $q->where('curso_id', $this->request->curso_id);
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

        return [
            $user->name,
            $user->email,
            $user->sede->nombre ?? 'N/A',
            $user->estamento->nombre ?? 'N/A',
            $cursosText ?: 'Sin cursos aprobados'
        ];
    }
}