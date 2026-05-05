<?php

namespace App\Http\Requests\Capacitador;

use Illuminate\Foundation\Http\FormRequest;

class StoreCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware `capacitador` garantiza el acceso
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
