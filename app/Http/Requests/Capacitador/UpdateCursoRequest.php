<?php

namespace App\Http\Requests\Capacitador;

use App\Models\Curso;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCursoRequest extends FormRequest
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
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'imagen_portada' => ['nullable', 'image', 'max:4096'],
            'color_promedio' => ['nullable', 'string', 'max:7'],
            'auto_color' => ['nullable', 'boolean'],
        ];
    }
}
