<?php

namespace App\Http\Requests\Capacitador;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSeccionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'secciones' => 'required|array',
            'secciones.*.id' => 'required',
            'secciones.*.modulos' => 'present|array',
            'modulos_sueltos' => 'present|array',
        ];
    }
}
