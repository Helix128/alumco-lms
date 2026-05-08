<?php

namespace Tests\Feature;

use App\Livewire\Support\ManageTickets;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class SupportTicketCountersCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_manage_tickets_uses_cached_counters(): void
    {
        $developer = User::factory()->create(['activo' => true]);
        $developer->assignRole('Desarrollador');

        Cache::shouldReceive('flexible')
            ->once()
            ->with('support_ticket_counters', [15, 60], \Closure::class)
            ->andReturn([
                'new' => 0,
                'critical' => 0,
                'waiting' => 0,
                'resolved_recent' => 0,
            ]);

        Livewire::actingAs($developer)
            ->test(ManageTickets::class)
            ->assertOk();
    }
}
