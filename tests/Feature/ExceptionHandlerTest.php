<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        Route::middleware('auth')->get('/_test/auth-required', fn () => response()->json(['ok' => true]));
        Route::middleware('auth')->get('/_test/forbidden', fn () => abort(403));
        Route::get('/_test/runtime-error', fn () => throw new RuntimeException('Boom interno'));
        Route::post('/_test/validation', function () {
            request()->validate([
                'nombre' => ['required', 'string'],
            ]);

            return response()->json(['ok' => true]);
        });
    }

    public function test_api_non_existent_route_returns_structured_404_json(): void
    {
        $response = $this->getJson('/ruta-que-no-existe');

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'error' => ['code', 'message', 'trace_id'],
            ])
            ->assertJsonPath('error.code', 404);
    }

    public function test_web_non_existent_route_returns_html_with_status_code(): void
    {
        $response = $this->get('/ruta-web-que-no-existe');

        $response->assertNotFound();
        $response->assertSee('404');
    }

    public function test_unauthenticated_access_to_protected_api_route_returns_401_json(): void
    {
        $response = $this->getJson('/_test/auth-required');

        $response
            ->assertUnauthorized()
            ->assertJsonStructure([
                'error' => ['code', 'message', 'trace_id'],
            ])
            ->assertJsonPath('error.code', 401);
    }

    public function test_unauthorized_access_returns_403_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/_test/forbidden');

        $response
            ->assertForbidden()
            ->assertJsonStructure([
                'error' => ['code', 'message', 'trace_id'],
            ])
            ->assertJsonPath('error.code', 403);
    }

    public function test_runtime_exception_returns_500_json_with_trace_id(): void
    {
        $response = $this->getJson('/_test/runtime-error');

        $response
            ->assertStatus(500)
            ->assertJsonStructure([
                'error' => ['code', 'message', 'trace_id'],
            ])
            ->assertJsonPath('error.code', 500);

        $this->assertNotEmpty($response->json('error.trace_id'));
    }

    public function test_validation_error_returns_422_with_top_level_trace_id(): void
    {
        $response = $this->postJson('/_test/validation', []);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
                'trace_id',
            ]);

        $this->assertNotEmpty($response->json('trace_id'));
    }

    public function test_all_json_error_responses_include_standard_error_keys(): void
    {
        $user = User::factory()->create();

        $responses = [
            $this->getJson('/ruta-json-inexistente'),
            $this->getJson('/_test/auth-required'),
            $this->actingAs($user)->getJson('/_test/forbidden'),
            $this->getJson('/_test/runtime-error'),
        ];

        foreach ($responses as $response) {
            $response->assertJsonStructure([
                'error' => ['code', 'message', 'trace_id'],
            ]);
        }
    }
}
