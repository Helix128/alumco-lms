<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HelpCenter
{
    /** @return array<string, array{title: string, summary: string, audience: list<string>, steps: list<string>, tips: list<string>}> */
    public function topics(): array
    {
        return [
            'iniciar-sesion' => $this->topic('Iniciar sesión', 'Accede con seguridad y reconoce cuándo el ingreso fue correcto.', ['public'], [
                'Escribe el correo registrado por tu organización.',
                'Ingresa tu contraseña y selecciona “Acceder al portal”.',
                'Espera la confirmación: el sistema abrirá el inicio correspondiente a tu rol.',
            ], ['Si el acceso falla, el formulario conserva tu correo y explica cómo corregir el problema.']),
            'recuperar-acceso' => $this->topic('Recuperar el acceso', 'Solicita un enlace seguro cuando olvides tu contraseña.', ['public'], [
                'En el inicio de sesión selecciona “¿Olvidaste tu contraseña?”.',
                'Escribe tu correo y envía la solicitud una sola vez.',
                'Abre el enlace recibido y define una contraseña nueva.',
            ], ['Si no llega el mensaje, revisa correo no deseado y luego contacta a soporte.']),
            'capacitaciones' => $this->topic('Mis capacitaciones', 'Encuentra una capacitación, revisa su vigencia y continúa tu avance.', ['authenticated'], [
                'Abre “Mis capacitaciones” desde el menú principal.',
                'Revisa el estado y el porcentaje de avance en cada tarjeta.',
                'Selecciona una capacitación para ver sus módulos y requisitos.',
            ], ['Los filtros permanecen visibles para que no tengas que recordar selecciones anteriores.']),
            'modulos' => $this->topic('Completar módulos', 'Consulta contenidos y registra su finalización.', ['authenticated'], [
                'Abre la capacitación y selecciona el siguiente módulo disponible.',
                'Revisa el contenido completo o descarga el archivo cuando esté permitido.',
                'Selecciona “Marcar como completado” y espera la confirmación.',
            ], ['Los módulos bloqueados indican qué requisito debes completar primero.']),
            'evaluaciones' => $this->topic('Evaluaciones', 'Responde, envía y comprende el resultado de una evaluación.', ['authenticated'], [
                'Lee el resumen de intentos y el porcentaje necesario para aprobar.',
                'Selecciona una respuesta en cada pregunta.',
                'Revisa las respuestas y selecciona “Enviar evaluación”.',
                'Lee el resultado y la acción recomendada antes de salir.',
            ], ['El capacitador puede usar Deshacer y Rehacer en el editor durante 30 minutos.']),
            'certificados' => $this->topic('Certificados', 'Descarga tus certificados o verifica un documento emitido.', ['authenticated'], [
                'Abre “Certificados” desde el menú.',
                'Ubica la capacitación completada.',
                'Selecciona “Descargar certificado”.',
            ], ['La emisión se confirma en pantalla; evita repetir la acción mientras se procesa.']),
            'verificar-certificado' => $this->topic('Verificar un certificado', 'Comprueba públicamente la autenticidad mediante su código.', ['public'], [
                'Abre el verificador público de certificados.',
                'Escribe el código exactamente como aparece en el documento.',
                'Selecciona “Verificar” y revisa el nombre, capacitación y fecha.',
            ], ['Un resultado no encontrado explica cómo revisar el código y contactar soporte.']),
            'soporte' => $this->topic('Contactar a soporte', 'Solicita ayuda en línea y consulta el estado de tu caso.', ['public'], [
                'Abre “Soporte” y describe el problema con lenguaje concreto.',
                'Adjunta evidencia sólo si no contiene datos sensibles innecesarios.',
                'Envía la solicitud y conserva el número de seguimiento.',
            ], ['Las personas autenticadas pueden responder y seguir su ticket desde el portal.']),
            'crear-contenido' => $this->topic('Crear contenido', 'Crea capacitaciones, secciones y módulos con validación y recuperación.', ['trainer'], [
                'Crea la capacitación y completa los campos obligatorios marcados.',
                'Organiza secciones y módulos en el orden de aprendizaje.',
                'Previsualiza como colaborador antes de comunicar la capacitación.',
            ], ['Usa Deshacer/Rehacer o Ctrl/Cmd+Z para corregir cambios recientes de estructura.']),
            'calendario' => $this->topic('Planificar en calendario', 'Programa fechas por sede y comunica cambios con claridad.', ['trainer'], [
                'Abre el calendario institucional y activa la edición si está disponible.',
                'Selecciona una capacitación, rango de fechas y sede.',
                'Guarda y espera la confirmación antes de continuar.',
            ], ['Mover, redimensionar, eliminar y copiar un año se pueden deshacer durante 30 minutos.']),
            'participantes' => $this->topic('Participantes', 'Asigna estamentos y revisa avance sin perder el contexto.', ['trainer'], [
                'Abre una capacitación y selecciona “Participantes”.',
                'Revisa la audiencia asignada y los estados en una misma pantalla.',
                'Exporta sólo cuando los filtros visibles coincidan con lo que necesitas.',
            ], ['La exportación informa que está generando el archivo y evita envíos duplicados.']),
            'reportes' => $this->topic('Reportes', 'Compara avance, certificación y sedes en una sola vista.', ['admin'], [
                'Define filtros de sede, estamento, capacitación y fecha.',
                'Compara los resultados visibles antes de exportar.',
                'Guarda un formato frecuente o genera el archivo.',
            ], ['Los formatos guardados admiten Deshacer/Rehacer y los filtros se conservan en la URL.']),
            'usuarios' => $this->topic('Usuarios y estamentos', 'Administra accesos y estructura organizacional de forma recuperable.', ['admin'], [
                'Busca a la persona o estamento antes de crear un registro nuevo.',
                'Revisa rol, sede y estado antes de guardar.',
                'Confirma eliminaciones y usa Deshacer cuando corresponda.',
            ], ['Los errores se muestran junto al campo y en un resumen enfocado.']),
            'herramientas-tecnicas' => $this->topic('Herramientas técnicas', 'Revisa salud del LMS y ejecuta tareas irreversibles con confirmación.', ['developer'], [
                'Abre “Salud LMS” y revisa primero el resumen del servicio afectado.',
                'Lee las consecuencias antes de limpiar caché o registros.',
                'Confirma sólo si la acción y el alcance son correctos.',
            ], ['Las tareas técnicas no tienen Deshacer; siempre ofrecen Cancelar y una consecuencia concreta.']),
        ];
    }

    /** @return Collection<string, array<string, mixed>> */
    public function allowedTopics(?User $user, ?string $search = null): Collection
    {
        $topics = collect($this->topics())->filter(fn (array $topic): bool => $this->canView($user, $topic['audience']));
        $search = trim((string) $search);

        if ($search === '') {
            return $topics;
        }

        $needle = Str::lower(Str::ascii($search));

        return $topics->filter(function (array $topic) use ($needle): bool {
            $haystack = Str::lower(Str::ascii(implode(' ', [
                $topic['title'], $topic['summary'], ...$topic['steps'], ...$topic['tips'],
            ])));

            return str_contains($haystack, $needle);
        });
    }

    /** @return array<string, mixed> */
    public function topicFor(?User $user, string $slug): array
    {
        $topic = $this->topics()[$slug] ?? null;
        abort_if($topic === null || ! $this->canView($user, $topic['audience']), 404);

        return $topic + ['slug' => $slug];
    }

    /** @param list<string> $audience */
    private function canView(?User $user, array $audience): bool
    {
        if (in_array('public', $audience, true)) {
            return true;
        }
        if ($user === null) {
            return false;
        }
        if (in_array('authenticated', $audience, true)) {
            return true;
        }
        if (in_array('trainer', $audience, true) && ($user->isCapacitador() || $user->hasAdminAccess())) {
            return true;
        }
        if (in_array('admin', $audience, true) && $user->hasAdminAccess()) {
            return true;
        }

        return in_array('developer', $audience, true) && $user->isDesarrollador();
    }

    /** @param list<string> $audience @param list<string> $steps @param list<string> $tips @return array<string, mixed> */
    private function topic(string $title, string $summary, array $audience, array $steps, array $tips): array
    {
        return compact('title', 'summary', 'audience', 'steps', 'tips');
    }
}
