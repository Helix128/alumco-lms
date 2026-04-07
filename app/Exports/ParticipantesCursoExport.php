<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ParticipantesCursoExport implements FromArray, WithHeadings
{
    public function __construct(private array $rows) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_keys($this->rows[0] ?? ['Nombre', 'Email', 'Estamento', 'Sede', 'Progreso (%)', 'Fecha Certificado']);
    }
}
