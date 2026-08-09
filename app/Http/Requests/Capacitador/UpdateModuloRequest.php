<?php

namespace App\Http\Requests\Capacitador;

use App\Models\Curso;
use App\Models\Modulo;
use App\Support\Capacitador\ModuloContentFileRules;
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

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
            'contenido' => ['nullable', 'string'],
            'ruta_archivo' => ModuloContentFileRules::forType($modulo?->tipo_contenido),
            'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'video_url' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ModuloContentFileRules::messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ModuloContentFileRules::attributes();
    }
}
