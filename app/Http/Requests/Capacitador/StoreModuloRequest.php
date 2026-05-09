<?php

namespace App\Http\Requests\Capacitador;

use App\Models\Curso;
use App\Models\Modulo;
use App\Support\Capacitador\ModuloContentFileRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuloRequest extends FormRequest
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
        $tipoContenido = $this->input('tipo_contenido', '');

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'tipo_contenido' => ['required', Rule::in(Modulo::TIPOS)],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
            'contenido' => ['nullable', 'string'],
            'ruta_archivo' => ModuloContentFileRules::forType($tipoContenido),
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
