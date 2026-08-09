<?php

namespace App\Support\Capacitador;

class ModuloContentFileRules
{
    /**
     * @return list<string>
     */
    public static function forType(?string $tipoContenido): array
    {
        $normalized = match ($tipoContenido) {
            'pdf' => 'pdf',
            'ppt', 'documento' => 'document',
            default => $tipoContenido,
        };
        $limit = (int) (config("media.limits.{$normalized}", config('media.limits.document')) / 1024);

        return [
            'nullable',
            'file',
            "max:{$limit}",
            ...match ($tipoContenido) {
                'video' => ['mimes:mp4'],
                'pdf' => ['mimes:pdf'],
                'ppt' => ['mimes:ppt,pptx'],
                'documento' => ['mimes:pdf,ppt,pptx,doc,docx'],
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
            'ruta_archivo.max' => 'El archivo supera el límite permitido para este tipo de contenido.',
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
