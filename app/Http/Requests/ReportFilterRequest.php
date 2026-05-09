<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAdminAccess();
    }

    public function rules(): array
    {
        return [
            'sede_id' => 'nullable|array|list',
            'sede_id.*' => 'integer|exists:sedes,id',
            'estamento_id' => 'nullable|array|list',
            'estamento_id.*' => 'integer|exists:estamentos,id',
            'curso_id' => 'nullable|array|list',
            'curso_id.*' => 'integer|exists:cursos,id',
            'edad_min' => 'nullable|integer|min:0|max:150',
            'edad_max' => 'nullable|integer|min:0|max:150',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado_capacitacion' => 'nullable|string|in:no_iniciado,en_progreso,certificado',
            'preset_id' => 'nullable|integer|exists:reporte_presets,id',
            'columnas' => 'nullable|array|list',
            'columnas.*' => 'string',
            'nombres' => 'nullable|array',
        ];
    }
}
