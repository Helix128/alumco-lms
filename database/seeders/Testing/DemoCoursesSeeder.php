<?php

namespace Database\Seeders\Testing;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCoursesSeeder extends Seeder
{
    private const PREVIOUS_DEMO_TITLES = [
        'Infecciones Intrahospitalarias',
        'Manejo de Residuos (REAS)',
        'RCP Básico',
        'Ley de Derechos del Paciente',
        'Higiene de Manos',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    private function courseTemplates(): array
    {
        return [
            'Profesionales' => [
                'titulo' => 'Infecciones Intrahospitalarias',
                'descripcion' => 'Medidas esenciales para prevenir infecciones asociadas a la atención de salud.',
                'color' => '#0f766e',
                'text_modules' => [
                    ['titulo' => 'Precauciones estándar en atención clínica', 'contenido' => 'Las precauciones estándar se aplican en toda atención, independientemente del diagnóstico. Antes de iniciar, evalúe el riesgo de contacto con sangre, fluidos corporales, secreciones, excreciones, salpicaduras o superficies contaminadas; con esa evaluación seleccione las barreras de protección que correspondan al procedimiento.', 'duracion' => 8],
                    ['titulo' => 'Higiene de manos en los momentos indicados', 'contenido' => 'Realice higiene de manos antes de tocar a la persona atendida y antes de un procedimiento limpio o aséptico; también después de exposición a fluidos corporales, de tocar a la persona y de tocar su entorno. Use preparación alcohólica cuando las manos no estén visiblemente sucias; use agua y jabón cuando presenten suciedad visible.', 'duracion' => 8],
                    ['titulo' => 'Control del entorno y reporte oportuno', 'contenido' => 'Limpie y desinfecte equipos compartidos y superficies de alto contacto según el protocolo local, entre usos cuando corresponda. Retire el equipo de protección de forma segura, descarte los insumos en el contenedor indicado y comunique de inmediato las exposiciones o incidentes para activar el procedimiento institucional.', 'duracion' => 7],
                ],
                'questions' => [
                    ['enunciado' => '¿Cuál es la medida base para cortar la transmisión de microorganismos?', 'opciones' => ['Higiene de manos antes y después de la atención', 'Reutilizar guantes limpios', 'Ventilar la sala una vez al día']],
                    ['enunciado' => '¿Cuándo se debe cambiar el equipo de protección personal?', 'opciones' => ['Al pasar entre procedimientos o pacientes según riesgo', 'Solo al finalizar el turno', 'Cuando lo solicite el paciente']],
                    ['enunciado' => '¿Qué acción reduce el riesgo en superficies clínicas?', 'opciones' => ['Limpiar y desinfectar con la frecuencia definida', 'Cubrirlas con papel durante toda la semana', 'Usar solo agua al cierre']],
                ],
            ],
            'Auxiliares de servicio' => [
                'titulo' => 'Manejo Seguro de Residuos REAS',
                'descripcion' => 'Clasificación, segregación y traslado seguro de residuos en establecimientos de atención de salud.',
                'color' => '#166534',
                'text_modules' => [
                    ['titulo' => 'Segregación en el punto de origen', 'contenido' => 'Clasifique y deposite cada residuo inmediatamente donde se genera, en el recipiente definido por el plan de manejo REAS del establecimiento. No mezcle categorías, no abra bolsas ya cerradas y no intente recuperar materiales desde un contenedor. La segregación correcta reduce los riesgos para pacientes, trabajadores y personal de transporte.', 'duracion' => 8],
                    ['titulo' => 'Manipulación y almacenamiento interno seguro', 'contenido' => 'Use los elementos de protección personal indicados para la tarea. Mantenga los recipientes rotulados, en buenas condiciones y sin sobrepasar su capacidad; ciérrelos antes de movilizarlos. Evite compactar bolsas con las manos, trasvasijar residuos o arrastrar contenedores de una forma que pueda producir derrames o exposición.', 'duracion' => 8],
                    ['titulo' => 'Traslado y respuesta ante incidentes', 'contenido' => 'Traslade los residuos por las rutas, horarios y carros definidos en el protocolo institucional, conservando su identificación y categoría. Ante un derrame, corte u otra exposición, asegure el área, aplique el procedimiento local, informe a la jefatura y registre el incidente. No continúe la tarea si no cuenta con condiciones seguras.', 'duracion' => 7],
                ],
                'questions' => [
                    ['enunciado' => '¿Dónde comienza la segregación correcta de residuos?', 'opciones' => ['En el lugar donde se genera el residuo', 'En la bodega central', 'Durante el retiro externo']],
                    ['enunciado' => '¿Qué información debe conservarse durante el traslado?', 'opciones' => ['La identificación y categoría del residuo', 'Solo el nombre del servicio', 'Una estimación sin rotulación']],
                    ['enunciado' => '¿Qué práctica evita una exposición accidental?', 'opciones' => ['No sobrellenar los contenedores', 'Compactar las bolsas manualmente', 'Mezclar residuos de aspecto similar']],
                ],
            ],
            'Manipuladores de alimentos' => [
                'titulo' => 'Higiene y Manipulación Segura de Alimentos',
                'descripcion' => 'Buenas prácticas de higiene, prevención de contaminación cruzada y control de temperaturas.',
                'color' => '#b45309',
                'text_modules' => [
                    ['titulo' => 'Las cinco claves de una preparación segura', 'contenido' => 'La preparación segura se basa en mantener la limpieza, separar alimentos crudos y cocidos, cocinar completamente, mantener temperaturas seguras y utilizar agua e insumos seguros. Lave las manos, utensilios y superficies al inicio y cada vez que una tarea pueda contaminar otra.', 'duracion' => 8],
                    ['titulo' => 'Prevención de contaminación cruzada', 'contenido' => 'Mantenga carnes, aves, pescados y sus jugos separados de los alimentos listos para consumo durante el almacenamiento y la preparación. Use utensilios y superficies higienizados entre tareas; conserve los alimentos crudos en recipientes cerrados o protegidos para evitar que sus líquidos entren en contacto con otros productos.', 'duracion' => 8],
                    ['titulo' => 'Temperaturas, materias primas y registro', 'contenido' => 'Cocine completamente los alimentos y controle las temperaturas en los puntos definidos por el establecimiento. No deje preparaciones perecibles a temperatura ambiente más tiempo del necesario; refrigere oportunamente y mantenga el refrigerador bajo 5 °C cuando corresponda. Utilice agua potable, materias primas en buen estado y registre los controles exigidos.', 'duracion' => 7],
                ],
                'questions' => [
                    ['enunciado' => '¿Cómo se evita la contaminación cruzada?', 'opciones' => ['Separando alimentos crudos de los listos para consumo', 'Usando el mismo utensilio para todo', 'Lavando las superficies solo al final del día']],
                    ['enunciado' => '¿Cuándo debe realizarse higiene de manos?', 'opciones' => ['Antes de preparar alimentos y después de manipular crudos', 'Solo al ingresar al recinto', 'Únicamente cuando las manos se ven sucias']],
                    ['enunciado' => '¿Qué dato debe registrarse durante la preparación?', 'opciones' => ['Las temperaturas definidas en los puntos de control', 'El color de cada utensilio', 'La cantidad de personas en el comedor']],
                ],
            ],
            'Asistentes de trato directo (ATD)' => [
                'titulo' => 'Primeros Auxilios y RCP para ATD',
                'descripcion' => 'Respuesta inicial segura ante emergencias, activación de ayuda y reanimación cardiopulmonar básica.',
                'color' => '#b91c1c',
                'text_modules' => [
                    ['titulo' => 'Seguridad de la escena y evaluación inicial', 'contenido' => 'Antes de acercarse, observe si existen peligros para usted, la persona afectada u otras personas; use los elementos de protección disponibles. Compruebe si responde y pida ayuda. La atención inicial no debe exponer al asistente a un riesgo evitable ni reemplazar la activación de los canales de emergencia del establecimiento.', 'duracion' => 8],
                    ['titulo' => 'Activación de ayuda y reconocimiento', 'contenido' => 'Si la persona adulta está inconsciente y no respira normalmente o solo jadea, active de inmediato el sistema de respuesta de emergencia y solicite un DEA. Una persona sola debe pedir ayuda primero y comenzar RCP sin demoras innecesarias; siga siempre el protocolo institucional y las indicaciones del operador de emergencias.', 'duracion' => 8],
                    ['titulo' => 'RCP y uso seguro del DEA', 'contenido' => 'Inicie compresiones torácicas de calidad según su capacitación mientras llega ayuda y utilice el DEA apenas esté disponible. Encienda el equipo, coloque los parches como indica el dispositivo y siga sus instrucciones de voz. Durante el análisis y antes de una descarga, confirme que nadie esté tocando a la persona.', 'duracion' => 7],
                ],
                'questions' => [
                    ['enunciado' => '¿Cuál es la primera acción ante una persona inconsciente?', 'opciones' => ['Comprobar la seguridad de la escena', 'Trasladarla inmediatamente', 'Darle agua para estimularla']],
                    ['enunciado' => '¿Qué debe hacerse al confirmar que no responde?', 'opciones' => ['Solicitar ayuda y activar el protocolo de emergencia', 'Esperar cinco minutos', 'Dejarla sola para buscar antecedentes']],
                    ['enunciado' => '¿Qué se confirma antes de una descarga con DEA?', 'opciones' => ['Que nadie esté tocando a la persona', 'Que todas las luces estén apagadas', 'Que la persona haya bebido agua']],
                ],
            ],
            'Personal de administración' => [
                'titulo' => 'Protección de Datos y Trato al Usuario',
                'descripcion' => 'Criterios para resguardar información personal y ofrecer una atención clara, respetuosa y confidencial.',
                'color' => '#1d4ed8',
                'text_modules' => [
                    ['titulo' => 'Finalidad y acceso autorizado a los datos', 'contenido' => 'Consulte, use o comunique datos personales únicamente cuando sea necesario para una función autorizada. La información de salud requiere un resguardo especialmente cuidadoso: no la comente en espacios abiertos, no la envíe por canales personales y no entregue antecedentes a personas cuya identidad o autorización no hayan sido verificadas.', 'duracion' => 8],
                    ['titulo' => 'Atención clara, respetuosa y confidencial', 'contenido' => 'Confirme la identidad antes de entregar información y comuníquese con lenguaje claro, respetuoso y acorde a las necesidades de la persona. Proteja la conversación de terceros, evite decir datos sensibles en voz alta y entregue orientación sobre el canal apropiado cuando la solicitud exceda sus atribuciones.', 'duracion' => 8],
                    ['titulo' => 'Manejo de incidentes de privacidad', 'contenido' => 'Si detecta un acceso, envío, pérdida o exposición indebida de información, no intente ocultarlo. Informe de inmediato por el canal institucional, conserve los antecedentes necesarios y siga las instrucciones para contener el incidente. El reporte oportuno permite proteger a la persona afectada y corregir el proceso.', 'duracion' => 7],
                ],
                'questions' => [
                    ['enunciado' => '¿Cuándo corresponde consultar datos personales?', 'opciones' => ['Cuando son necesarios para una tarea autorizada', 'Siempre que resulten interesantes', 'Cuando los solicita cualquier compañero']],
                    ['enunciado' => '¿Cómo debe entregarse información a una persona usuaria?', 'opciones' => ['En lenguaje claro y resguardando su privacidad', 'En voz alta para agilizar la atención', 'Solo mediante canales personales']],
                    ['enunciado' => '¿Qué se debe hacer ante un envío de datos al destinatario equivocado?', 'opciones' => ['Reportarlo de inmediato según el protocolo', 'Borrar el correo y no informar', 'Esperar a que el destinatario reclame']],
                ],
            ],
        ];
    }

    public function run(): void
    {
        $admin = User::query()->where('email', env('SEED_ADMIN_EMAIL', 'admin@alumco.cl'))->first();
        $estamentos = Estamento::query()->orderBy('id')->get();

        if (! $admin || $estamentos->isEmpty()) {
            return;
        }

        $desiredTitles = $estamentos
            ->map(fn (Estamento $estamento, int $index): string => $this->courseFor($estamento, $index)['titulo'])
            ->all();

        Curso::query()
            ->where('capacitador_id', $admin->id)
            ->whereIn('titulo', self::PREVIOUS_DEMO_TITLES)
            ->whereNotIn('titulo', $desiredTitles)
            ->delete();

        foreach ($estamentos as $index => $estamento) {
            $courseData = $this->courseFor($estamento, $index);
            $curso = Curso::query()->updateOrCreate(
                ['titulo' => $courseData['titulo']],
                [
                    'descripcion' => $courseData['descripcion'],
                    'imagen_portada' => null,
                    'color_promedio' => $courseData['color'],
                    'capacitador_id' => $admin->id,
                ]
            );

            $curso->estamentos()->sync([$estamento->id]);
            $this->removeMedia($curso, 'cover');
            $this->syncPlanning($curso, $estamento);
            $this->syncModules($curso, $courseData);
        }
    }

    /** @return array<string, mixed> */
    private function courseFor(Estamento $estamento, int $index): array
    {
        $templates = $this->courseTemplates();

        if (isset($templates[$estamento->nombre])) {
            return $templates[$estamento->nombre];
        }

        $colors = ['#0f766e', '#166534', '#b45309', '#b91c1c', '#1d4ed8', '#6d28d9'];

        return [
            'titulo' => 'Inducción Esencial: '.$estamento->nombre,
            'descripcion' => 'Capacitación introductoria con prácticas esenciales para el estamento '.$estamento->nombre.'.',
            'color' => $colors[$index % count($colors)],
            'text_modules' => [
                ['titulo' => 'Responsabilidades y buenas prácticas', 'contenido' => 'Conozca las responsabilidades de su cargo, siga los protocolos institucionales vigentes y solicite orientación cuando una situación exceda sus atribuciones. Una práctica segura combina preparación, comunicación clara y respeto por las personas atendidas y el equipo de trabajo.', 'duracion' => 8],
                ['titulo' => 'Prevención y comunicación de incidentes', 'contenido' => 'Identifique riesgos antes de iniciar una tarea y utilice las medidas de control disponibles. Si ocurre un incidente o detecta una condición insegura, comuníquelo tan pronto como sea seguro hacerlo por el canal definido; informar a tiempo ayuda a prevenir daños y a mejorar los procesos.', 'duracion' => 8],
                ['titulo' => 'Consulta de instrucciones vigentes', 'contenido' => 'Utilice los protocolos, procedimientos y canales institucionales como fuente de orientación. Evite basarse en mensajes informales o documentos sin vigencia confirmada. Ante dudas, detenga la acción que pueda generar riesgo y consulte a la jefatura o al referente correspondiente.', 'duracion' => 7],
            ],
            'questions' => [
                ['enunciado' => '¿Qué debe hacerse ante una situación fuera de las atribuciones del cargo?', 'opciones' => ['Solicitar orientación por el canal definido', 'Improvisar una solución sin informar', 'Ignorar la situación']],
                ['enunciado' => '¿Cuándo se reporta un incidente?', 'opciones' => ['Tan pronto como sea seguro hacerlo', 'Solo al finalizar el mes', 'Únicamente si existe un reclamo']],
                ['enunciado' => '¿Dónde se consultan las instrucciones de trabajo?', 'opciones' => ['En los protocolos institucionales vigentes', 'En mensajes personales sin validar', 'En documentos antiguos sin fecha']],
            ],
        ];
    }

    /** @param array<string, mixed> $courseData */
    private function syncModules(Curso $curso, array $courseData): void
    {
        $modules = array_map(
            fn (array $module): array => ['tipo' => 'texto', ...$module],
            $courseData['text_modules']
        );
        $modules[] = ['tipo' => 'evaluacion', 'titulo' => 'Evaluación: '.$courseData['titulo']];

        foreach ($modules as $index => $moduleData) {
            $modulo = Modulo::query()->updateOrCreate(
                ['curso_id' => $curso->id, 'orden' => $index + 1],
                [
                    'titulo' => $moduleData['titulo'],
                    'tipo_contenido' => $moduleData['tipo'],
                    'ruta_archivo' => null,
                    'nombre_archivo_original' => null,
                    'contenido' => $moduleData['contenido'] ?? null,
                    'duracion_minutos' => $moduleData['duracion'] ?? null,
                ]
            );

            if ($modulo->tipo_contenido === 'evaluacion') {
                $this->removeMedia($modulo, 'content');
                $this->syncEvaluation($modulo, $courseData['questions']);
            } else {
                $this->removeMedia($modulo, 'content');
                $modulo->evaluacion()->delete();
            }
        }

        $curso->modulos()->where('orden', '>', count($modules))->delete();
    }

    private function syncPlanning(Curso $curso, Estamento $estamento): void
    {
        $startsAt = now()->setDate(now()->year, 8, 9)->startOfDay();
        $endsAt = $startsAt->copy()->addDays(6)->endOfDay();

        $planificacion = PlanificacionCurso::query()->updateOrCreate(
            ['curso_id' => $curso->id, 'sede_id' => null],
            [
                'fecha_inicio' => $startsAt->toDateString(),
                'fecha_fin' => $endsAt->toDateString(),
                'notas' => 'Bloque demo para '.$estamento->nombre.': semana del 9 al 15 de agosto.',
            ]
        );

        PlanificacionCurso::query()
            ->where('curso_id', $curso->id)
            ->whereKeyNot($planificacion->id)
            ->delete();
    }

    private function removeMedia(Curso|Modulo $target, string $collection): void
    {
        $target->mediaAttachments()->where('collection', $collection)->delete();
    }

    /** @param array<int, array<string, mixed>> $questions */
    private function syncEvaluation(Modulo $modulo, array $questions): void
    {
        $evaluacion = Evaluacion::query()->firstOrCreate(['modulo_id' => $modulo->id]);

        foreach ($questions as $questionIndex => $questionData) {
            $pregunta = Pregunta::query()->updateOrCreate(
                ['evaluacion_id' => $evaluacion->id, 'orden' => $questionIndex + 1],
                ['enunciado' => $questionData['enunciado']]
            );

            foreach ($questionData['opciones'] as $optionIndex => $texto) {
                Opcion::query()->updateOrCreate(
                    ['pregunta_id' => $pregunta->id, 'orden' => $optionIndex + 1],
                    ['texto' => $texto, 'es_correcta' => $optionIndex === 0]
                );
            }

            $pregunta->opciones()->where('orden', '>', count($questionData['opciones']))->delete();
        }

        $evaluacion->preguntas()->where('orden', '>', count($questions))->delete();
    }
}
