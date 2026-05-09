<?php

namespace App\Support\Capacitador;

class ModuloContentFileRules
{
    /**
     * @return list<string>
     */
    public static function forType(?string $tipoContenido): array
    {
        return [
            'nullable',
            'file',
            'max:512000',
            ...match ($tipoContenido) {
                'video' => ['mimes:mp4'],
                'pdf' => ['mimes:pdf'],
                'ppt' => ['mimes:ppt,pptx'],
                'imagen' => ['mimes:jpeg,png,jpg,gif,webp'],
                default => [],
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'ruta_archivo.mimes' => 'El archivo no corresponde al tipo de contenido seleccionado para el modulo.',
            'ruta_archivo.max' => 'El archivo del modulo no puede superar 500 MB.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'ruta_archivo' => 'archivo del modulo',
            'tipo_contenido' => 'tipo de contenido',
            'duracion_minutos' => 'duracion estimada',
        ];
    }
}
