<?php

namespace App\Http\Requests\Capacitador;

use App\Models\Curso;
use App\Support\Capacitador\CursoContentRules;
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
        return CursoContentRules::rules();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return CursoContentRules::messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return CursoContentRules::attributes();
    }
}
