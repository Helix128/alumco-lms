<?php

namespace App\Support\Capacitador;

class CursoContentRules
{
    /**
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'nota_capacitador' => ['nullable', 'string', 'max:1200'],
            'imagen_portada' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'color_promedio' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'auto_color' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'color_promedio.regex' => 'El color de portada debe estar en formato hexadecimal, por ejemplo #1a3a5a.',
            'imagen_portada.mimes' => 'La portada debe ser una imagen JPG, PNG o WebP.',
            'nota_capacitador.max' => 'La nota del capacitador no puede superar 1200 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'titulo' => 'titulo del curso',
            'descripcion' => 'descripcion',
            'nota_capacitador' => 'nota del capacitador',
            'imagen_portada' => 'portada del curso',
            'color_promedio' => 'color de portada',
            'auto_color' => 'color automatico',
        ];
    }
}
