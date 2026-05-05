<?php

namespace App\Http\Requests\Capacitador;

use App\Models\Curso;
use App\Models\Modulo;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Curso $curso */
        $curso = $this->route('curso');

        return $this->user()->can('manage', $curso);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Modulo $modulo */
        $modulo = $this->route('modulo');

        $mimeRules = match ($modulo?->tipo_contenido) {
            'video' => 'mimes:mp4',
            'pdf' => 'mimes:pdf',
            'ppt' => 'mimes:ppt,pptx',
            'imagen' => 'mimes:jpeg,png,jpg,gif,webp',
            default => '',
        };

        $fileRule = 'nullable|file|max:512000'.($mimeRules ? '|'.$mimeRules : '');

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
            'contenido' => ['nullable', 'string'],
            'ruta_archivo' => $fileRule,
        ];
    }
}
