<?php

namespace Tests\Feature\Admin;

use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class ReportsTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_an_admin_can_access_reports_page()
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.reportes.index'))
            ->assertOk()
            ->assertSee('Análisis de Cumplimiento');
    }

    public function test_a_worker_cannot_access_reports_page()
    {
        $trabajador = $this->createTrabajador();

        $this->actingAs($trabajador)
            ->get(route('admin.reportes.index'))
            ->assertForbidden();
    }

    public function test_reports_can_be_filtered_by_sede()
    {
        $admin = $this->createAdmin();
        $sedeA = Sede::create(['nombre' => 'Sede A']);
        $sedeB = Sede::create(['nombre' => 'Sede B']);
        $estamento = Estamento::create(['nombre' => 'Test']);

        User::factory()->count(3)->create(['sede_id' => $sedeA->id, 'estamento_id' => $estamento->id]);
        User::factory()->count(2)->create(['sede_id' => $sedeB->id, 'estamento_id' => $estamento->id]);

        $this->actingAs($admin)
            ->get(route('admin.reportes.index', ['sede_id' => [$sedeA->id]]))
            ->assertOk()
            ->assertSee('3 registros encontrados')
            ->assertDontSee('5 registros encontrados');
    }

    public function test_reports_can_be_filtered_by_age_range()
    {
        $admin = $this->createAdmin();
        $estamento = Estamento::create(['nombre' => 'Test']);

        // Usuario de 25 años
        User::factory()->create([
            'estamento_id' => $estamento->id,
            'fecha_nacimiento' => Carbon::now()->subYears(25)->format('Y-m-d'),
        ]);

        // Usuario de 50 años
        User::factory()->create([
            'estamento_id' => $estamento->id,
            'fecha_nacimiento' => Carbon::now()->subYears(50)->format('Y-m-d'),
        ]);

        // Filtrar rango 20-30
        $this->actingAs($admin)
            ->get(route('admin.reportes.index', ['edad_min' => 20, 'edad_max' => 30]))
            ->assertOk()
            ->assertSee('1 registros encontrados');

        // Filtrar rango 40-60
        $this->actingAs($admin)
            ->get(route('admin.reportes.index', ['edad_min' => 40, 'edad_max' => 60]))
            ->assertOk()
            ->assertSee('1 registros encontrados');
    }

    public function test_reports_excel_export_contains_correct_data()
    {
        $admin = $this->createAdmin();
        $sede = Sede::create(['nombre' => 'Sede Especial']);
        $estamento = Estamento::create(['nombre' => 'Estamento Especial']);

        User::factory()->create([
            'name' => 'Juan Reporte',
            'email' => 'juan@reporte.cl',
            'sede_id' => $sede->id,
            'estamento_id' => $estamento->id,
            'fecha_nacimiento' => '1990-01-01',
        ]);

        // Usamos la configuración de columnas del request para el test
        $params = [
            'columnas' => ['nombre', 'email', 'sede'],
            'nombres' => [
                'nombre' => 'Columna Nombre',
                'email' => 'Columna Email',
                'sede' => 'Columna Sede',
            ],
        ];

        $response = $this->actingAs($admin)->get(route('admin.reportes.exportar', $params));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=reporte_capacitaciones.xlsx');

        // Para inspeccionar el contenido, necesitamos usar el facade Excel en modo real (no fake)
        // y leer el archivo temporal generado por la respuesta de Laravel
        $filePath = $response->getFile()->getPathname();

        $data = Excel::toArray(new \stdClass, $filePath);
        $rows = $data[0]; // Primera hoja

        // Verificar encabezados
        $this->assertEquals('Columna Nombre', $rows[0][0]);
        $this->assertEquals('Columna Email', $rows[0][1]);
        $this->assertEquals('Columna Sede', $rows[0][2]);

        // Verificar primer registro de datos
        $this->assertEquals('Juan Reporte', $rows[1][0]);
        $this->assertEquals('juan@reporte.cl', $rows[1][1]);
        $this->assertEquals('Sede Especial', $rows[1][2]);
    }
}
