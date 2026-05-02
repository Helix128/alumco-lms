<?php

namespace Database\Seeders\Testing;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoCoursesSeeder extends Seeder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    private function courses(): array
    {
        return [
            [
                'titulo' => 'Infecciones Intrahospitalarias',
                'descripcion' => 'Medidas esenciales para prevenir infecciones asociadas a la atención de salud.',
                'cover' => 'infecciones.svg',
                'color' => '#0f766e',
                'modules' => [
                    ['tipo' => 'video', 'titulo' => 'Cadena de transmisión y puntos críticos', 'archivo' => 'videos/cadena-transmision.mp4', 'duracion' => 14],
                    ['tipo' => 'texto', 'titulo' => 'Precauciones estándar', 'contenido' => 'Aplicación de higiene de manos, barreras de protección y manejo seguro de insumos clínicos.', 'duracion' => 10],
                    ['tipo' => 'pdf', 'titulo' => 'Checklist de prevención IAAS', 'archivo' => 'documentos/checklist-iaas.pdf', 'duracion' => 8],
                    ['tipo' => 'evaluacion', 'titulo' => 'Evaluación de prevención IAAS'],
                ],
                'questions' => [
                    ['enunciado' => '¿Cuál es la medida base para cortar la transmisión de microorganismos?', 'opciones' => ['Higiene de manos antes y después de la atención', 'Reutilizar guantes limpios', 'Ventilar la sala una vez al día']],
                    ['enunciado' => '¿Cuándo se debe cambiar el equipo de protección personal?', 'opciones' => ['Al pasar entre procedimientos o pacientes según riesgo', 'Solo al finalizar el turno', 'Cuando lo solicite el paciente']],
                    ['enunciado' => '¿Qué acción reduce el riesgo en superficies clínicas?', 'opciones' => ['Limpieza y desinfección con frecuencia definida', 'Cubrirlas con papel durante toda la semana', 'Usar solo agua al cierre']],
                ],
            ],
            [
                'titulo' => 'Manejo de Residuos (REAS)',
                'descripcion' => 'Clasificación, segregación y traslado seguro de residuos de establecimientos de atención de salud.',
                'cover' => 'residuos.svg',
                'color' => '#166534',
                'modules' => [
                    ['tipo' => 'ppt', 'titulo' => 'Clasificación de residuos', 'archivo' => 'presentaciones/clasificacion-reas.pptx', 'duracion' => 12],
                    ['tipo' => 'imagen', 'titulo' => 'Infografía de contenedores', 'archivo' => 'imagenes/contenedores-reas.webp', 'duracion' => 6],
                    ['tipo' => 'texto', 'titulo' => 'Flujo de retiro interno', 'contenido' => 'Separar en origen, rotular correctamente y respetar rutas internas autorizadas.', 'duracion' => 9],
                    ['tipo' => 'evaluacion', 'titulo' => 'Evaluación de manejo REAS'],
                ],
                'questions' => [
                    ['enunciado' => '¿Dónde comienza la segregación correcta de residuos?', 'opciones' => ['En el punto donde se genera el residuo', 'En la bodega central', 'Al momento del retiro externo']],
                    ['enunciado' => '¿Qué información debe conservarse en el traslado interno?', 'opciones' => ['Identificación y categoría del residuo', 'Solo el nombre del servicio', 'La cantidad aproximada sin rotulación']],
                    ['enunciado' => '¿Qué práctica evita exposición accidental?', 'opciones' => ['No sobrellenar contenedores', 'Compactar manualmente bolsas', 'Mezclar residuos similares']],
                ],
            ],
            [
                'titulo' => 'RCP Básico',
                'descripcion' => 'Respuesta inicial ante paro cardiorrespiratorio y uso coordinado del desfibrilador externo automático.',
                'cover' => 'rcp.svg',
                'color' => '#b91c1c',
                'modules' => [
                    ['tipo' => 'video', 'titulo' => 'Reconocimiento de paro y activación de ayuda', 'archivo' => 'videos/rcp-activacion.mp4', 'duracion' => 11],
                    ['tipo' => 'texto', 'titulo' => 'Compresiones de alta calidad', 'contenido' => 'Mantener ritmo constante, profundidad adecuada y relevo oportuno entre participantes.', 'duracion' => 10],
                    ['tipo' => 'ppt', 'titulo' => 'Uso seguro de DEA', 'archivo' => 'presentaciones/uso-dea.pptx', 'duracion' => 9],
                    ['tipo' => 'evaluacion', 'titulo' => 'Evaluación de RCP básico'],
                ],
                'questions' => [
                    ['enunciado' => '¿Cuál es la primera acción al encontrar una persona inconsciente?', 'opciones' => ['Evaluar seguridad de la escena y respuesta', 'Iniciar traslado inmediato', 'Dar agua para estimular']],
                    ['enunciado' => '¿Qué se busca con compresiones de calidad?', 'opciones' => ['Mantener perfusión hasta recuperar circulación', 'Evitar pedir ayuda externa', 'Reemplazar el uso del DEA']],
                    ['enunciado' => '¿Qué debe hacerse antes de una descarga con DEA?', 'opciones' => ['Confirmar que nadie toque a la persona', 'Retirar a todo el equipo del recinto', 'Apagar todas las luces']],
                ],
            ],
            [
                'titulo' => 'Ley de Derechos del Paciente',
                'descripcion' => 'Principios de trato digno, información, consentimiento y confidencialidad en la atención.',
                'cover' => 'derechos.svg',
                'color' => '#1d4ed8',
                'modules' => [
                    ['tipo' => 'pdf', 'titulo' => 'Resumen normativo', 'archivo' => 'documentos/derechos-paciente.pdf', 'duracion' => 12],
                    ['tipo' => 'texto', 'titulo' => 'Trato digno y comunicación clara', 'contenido' => 'La atención debe considerar identidad, privacidad, información comprensible y registro oportuno.', 'duracion' => 8],
                    ['tipo' => 'evaluacion', 'titulo' => 'Evaluación de derechos del paciente'],
                ],
                'questions' => [
                    ['enunciado' => '¿Qué exige el trato digno?', 'opciones' => ['Respeto, privacidad e información comprensible', 'Atención sin explicar procedimientos', 'Registrar solo si existe reclamo']],
                    ['enunciado' => '¿Qué resguarda la confidencialidad?', 'opciones' => ['Información clínica y datos personales', 'Solo resultados de laboratorio', 'Únicamente datos administrativos']],
                    ['enunciado' => '¿Cuándo debe entregarse información al paciente?', 'opciones' => ['Durante el proceso de atención en lenguaje claro', 'Solo después del alta', 'Solo si el paciente firma un reclamo']],
                ],
            ],
            [
                'titulo' => 'Higiene de Manos',
                'descripcion' => 'Momentos, técnica y monitoreo de adherencia para higiene de manos en áreas clínicas.',
                'cover' => 'higiene.svg',
                'color' => '#0369a1',
                'modules' => [
                    ['tipo' => 'imagen', 'titulo' => 'Cinco momentos de higiene', 'archivo' => 'imagenes/cinco-momentos.webp', 'duracion' => 5],
                    ['tipo' => 'video', 'titulo' => 'Técnica con solución alcohólica', 'archivo' => 'videos/tecnica-higiene.mp4', 'duracion' => 7],
                    ['tipo' => 'texto', 'titulo' => 'Errores frecuentes', 'contenido' => 'Evitar joyas, uñas largas, omitir pulgares o retirar la solución antes del secado completo.', 'duracion' => 6],
                    ['tipo' => 'evaluacion', 'titulo' => 'Evaluación de higiene de manos'],
                ],
                'questions' => [
                    ['enunciado' => '¿Qué debe ocurrir antes del contacto con el paciente?', 'opciones' => ['Higiene de manos según los cinco momentos', 'Uso permanente del mismo par de guantes', 'Limpieza solo al finalizar la atención']],
                    ['enunciado' => '¿Qué zona suele omitirse en la técnica?', 'opciones' => ['Pulgares y espacios interdigitales', 'Antebrazos completos', 'Codos y hombros']],
                    ['enunciado' => '¿Cuándo finaliza la fricción con alcohol gel?', 'opciones' => ['Cuando las manos están secas', 'A los tres segundos exactos', 'Al ponerse guantes encima']],
                ],
            ],
        ];
    }

    public function run(): void
    {
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@alumco.cl');
        $admin = User::query()->where('email', $adminEmail)->first();

        if (! $admin) {
            return;
        }

        foreach ($this->courses() as $courseIndex => $courseData) {
            $coverPath = $this->copyCover($courseData['cover']);

            $curso = Curso::query()->updateOrCreate(
                ['titulo' => $courseData['titulo']],
                [
                    'descripcion' => $courseData['descripcion'],
                    'imagen_portada' => $coverPath,
                    'color_promedio' => $courseData['color'],
                    'capacitador_id' => $admin->id,
                ]
            );

            $this->syncPlanning($curso, $courseIndex);
            $this->syncModules($curso, $courseData['modules'], $courseData['questions']);
        }
    }

    private function copyCover(string $fileName): string
    {
        $source = database_path("seeders/assets/course-covers/{$fileName}");
        $target = "portadas/demo/{$fileName}";

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }

    private function ensureDemoPdf(string $path, string $title): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->put($path, $this->demoPdfContent($title));
    }

    private function demoPdfContent(string $title): string
    {
        $safeTitle = str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $title);
        $stream = "BT /F1 18 Tf 72 720 Td (Documento demo Alumco LMS) Tj 0 -32 Td ({$safeTitle}) Tj ET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}endstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= 'trailer << /Root 1 0 R /Size '.(count($objects) + 1)." >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    private function syncPlanning(Curso $curso, int $courseIndex): void
    {
        $startsAt = now()->startOfWeek()->addWeeks($courseIndex);
        $endsAt = $startsAt->copy()->endOfWeek();

        $planificacion = PlanificacionCurso::query()->updateOrCreate(
            [
                'curso_id' => $curso->id,
                'sede_id' => null,
            ],
            [
                'fecha_inicio' => $startsAt->toDateString(),
                'fecha_fin' => $endsAt->toDateString(),
                'notas' => 'Planificación demo semanal para todas las sedes.',
            ]
        );

        PlanificacionCurso::query()
            ->where('curso_id', $curso->id)
            ->whereKeyNot($planificacion->id)
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $modules
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function syncModules(Curso $curso, array $modules, array $questions): void
    {
        foreach ($modules as $index => $moduleData) {
            $modulo = Modulo::query()->updateOrCreate(
                [
                    'curso_id' => $curso->id,
                    'orden' => $index + 1,
                ],
                [
                    'titulo' => $moduleData['titulo'],
                    'tipo_contenido' => $moduleData['tipo'],
                    'ruta_archivo' => $moduleData['archivo'] ?? null,
                    'nombre_archivo_original' => isset($moduleData['archivo']) ? basename($moduleData['archivo']) : null,
                    'contenido' => $moduleData['contenido'] ?? null,
                    'duracion_minutos' => $moduleData['duracion'] ?? null,
                ]
            );

            if ($modulo->tipo_contenido === 'evaluacion') {
                $this->syncEvaluation($modulo, $questions);
            } else {
                $modulo->evaluacion()->delete();

                if ($modulo->tipo_contenido === 'pdf' && $modulo->ruta_archivo) {
                    $this->ensureDemoPdf($modulo->ruta_archivo, $modulo->titulo);
                }
            }
        }

        $curso->modulos()->where('orden', '>', count($modules))->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function syncEvaluation(Modulo $modulo, array $questions): void
    {
        $evaluacion = Evaluacion::query()->firstOrCreate(['modulo_id' => $modulo->id]);

        foreach ($questions as $questionIndex => $questionData) {
            $pregunta = Pregunta::query()->updateOrCreate(
                [
                    'evaluacion_id' => $evaluacion->id,
                    'orden' => $questionIndex + 1,
                ],
                ['enunciado' => $questionData['enunciado']]
            );

            foreach ($questionData['opciones'] as $optionIndex => $texto) {
                Opcion::query()->updateOrCreate(
                    [
                        'pregunta_id' => $pregunta->id,
                        'orden' => $optionIndex + 1,
                    ],
                    [
                        'texto' => $texto,
                        'es_correcta' => $optionIndex === 0,
                    ]
                );
            }

            $pregunta->opciones()->where('orden', '>', count($questionData['opciones']))->delete();
        }

        $evaluacion->preguntas()->where('orden', '>', count($questions))->delete();
    }
}
