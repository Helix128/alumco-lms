<?php

namespace App\Http\Requests\Capacitador;

use App\Support\Capacitador\CursoContentRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
