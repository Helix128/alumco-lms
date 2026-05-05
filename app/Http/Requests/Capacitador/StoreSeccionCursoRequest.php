<?php

namespace App\Http\Requests\Capacitador;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeccionCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
        ];
    }
}
