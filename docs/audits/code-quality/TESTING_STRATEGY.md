# Estrategia de Testing

## Estado Actual
El proyecto tiene una base de pruebas mejor que la media para una app en crecimiento: 20 archivos Feature, factories para entidades principales y uso recurrente de `RefreshDatabase`, `Storage::fake()`, `Notification::fake()` y `Livewire::test()`. Hay cobertura relevante para certificados, notificaciones, calendario, reportes, accesibilidad, navegación y jerarquía admin. El gap principal no es “no hay tests”, sino falta de pruebas de seguridad específicas, rendimiento de queries y CI que las ejecute automáticamente.

## Qué Testear Primero

1. **Dependencias y exportación Excel**
   - Ejecutar `composer audit` en CI.
   - Mantener `tests/Feature/Admin/ReportsTest.php` como prueba de regresión tras actualizar PhpSpreadsheet.

2. **Login sin elevación de privilegios**
   - Confirmar que `AuthController@login` no asigna roles por email.
   - Verificar que usuarios privilegiados solo son privilegiados si ya tienen rol.

3. **Autorización de evaluación Livewire**
   - `EditarEvaluacion` debe impedir borrar o modificar preguntas/opciones de otra evaluación.

4. **Sanitización XSS de módulos tipo texto**
   - Crear módulo con `<script>`, `onerror`, `javascript:` y verificar que no aparece en HTML final.

5. **Participantes sin N+1**
   - Test de query count o test de repositorio que confirme cálculo agrupado.

## Ejemplos de Feature Tests Prioritarios

### 1. Login no asigna roles por email
```php
public function test_login_does_not_assign_privileged_roles_by_email(): void
{
    $user = User::factory()->create([
        'email' => 'admin@alumco.cl',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => 'admin@alumco.cl',
        'password' => 'password',
    ]);

    $this->assertFalse($user->fresh()->hasRole('Administrador'));
}
```

### 2. No puede borrar pregunta ajena
```php
public function test_cannot_delete_question_from_another_evaluation(): void
{
    $own = Evaluacion::factory()->create();
    $foreignQuestion = Pregunta::factory()->create();

    Livewire::actingAs($this->capacitador)
        ->test(EditarEvaluacion::class, ['evaluacion' => $own, 'curso' => $this->curso])
        ->call('eliminarPregunta', $foreignQuestion->id);

    $this->assertModelExists($foreignQuestion);
}
```

### 3. Sanitiza contenido HTML peligroso
```php
public function test_module_html_content_is_sanitized(): void
{
    Storage::fake('public');
    $capacitador = User::factory()->create();
    $capacitador->assignRole('Capacitador Interno');
    $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);

    $this->actingAs($capacitador)->post(route('capacitador.cursos.modulos.store', $curso), [
        'titulo' => 'Texto',
        'tipo_contenido' => 'texto',
        'contenido' => '<img src=x onerror=alert(1)><script>alert(1)</script>',
    ])->assertRedirect();

    $modulo = Modulo::latest()->first();

    $this->actingAs($capacitador)
        ->get(route('modulos.show', [$curso, $modulo]))
        ->assertDontSee('<script>', false)
        ->assertDontSee('onerror', false);
}
```

### 4. Exportación de reportes sigue funcionando tras actualizar dependencias
```php
public function test_reports_export_downloads_xlsx(): void
{
    $admin = $this->createAdmin();

    $this->actingAs($admin)
        ->get(route('admin.reportes.exportar'))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=reporte_capacitaciones.xlsx');
}
```

### 5. Participantes calcula progreso sin consultas por usuario
```php
public function test_participants_page_shows_progress_for_multiple_users(): void
{
    $capacitador = $this->createCapacitadorInterno();
    $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);
    $modulos = Modulo::factory()->count(2)->create(['curso_id' => $curso->id]);
    $users = User::factory()->count(3)->create();

    // Asociar estamentos y progresos...

    $this->actingAs($capacitador)
        ->get(route('capacitador.cursos.participantes.index', $curso))
        ->assertOk()
        ->assertSee('50%');
}
```

## Unit Tests Recomendados

### `AverageCourseCoverColor`
- Imagen válida JPEG/PNG/WebP.
- Archivo inexistente devuelve `null`.
- Imagen gris/oscura cae en fallback.
- No toca el modelo ni storage global fuera del contrato.

### `CertificateEligibility`
- Curso sin evaluaciones permite certificado si progreso está completo.
- Curso con evaluación requiere intento aprobado.
- Usuario sin aprobación lanza excepción de dominio.

### `CalendarGridBuilder`
- Cruces de mes/año.
- Planificaciones globales vs sede específica.
- Detección de conflictos por semana.
- Rango anual con semana 1 que inicia en diciembre.

### `CourseParticipantProgressQuery`
- Usuarios duplicados por múltiples estamentos se deduplican.
- Progreso calcula 0, parcial, 100.
- Certificado se adjunta solo para el curso solicitado.

## Configuración Recomendada de `phpunit.xml`

La configuración actual es correcta como base:
```xml
<env name="APP_ENV" value="testing"/>
<env name="CACHE_STORE" value="array"/>
<env name="MAIL_MAILER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
```

Recomendación adicional:
```xml
<env name="APP_DEBUG" value="true"/>
<env name="DB_CONNECTION" value="mysql"/>
```

Si se quiere una suite más rápida en CI, evaluar sqlite solo después de confirmar compatibilidad con migraciones que usan MySQL enum/raw SQL.

## CI/CD con Tests Automáticos

Crear `.github/workflows/tests.yml`:
```yaml
name: Tests

on:
  pull_request:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.4
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping -ppassword"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo_mysql, gd, zip, redis
      - run: composer install --no-interaction --prefer-dist
      - run: composer audit
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: vendor/bin/pint --test
      - run: php artisan test --compact
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
```

## Comandos Locales con Sail
```bash
./vendor/bin/sail composer audit
./vendor/bin/sail bin pint --dirty --format agent
./vendor/bin/sail artisan test --compact
./vendor/bin/sail artisan test --compact tests/Feature/Admin/ReportsTest.php
```
