<?php

namespace Tests\Feature;

use App\Actions\Support\CreateSupportTicketAction;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\SupportTicketCreatedNotification;
use App\Notifications\SupportTicketRequesterNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SupportTicketNotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_support_ticket_notifications_are_dispatched_after_commit(): void
    {
        Notification::fake();
        $developer = User::factory()->create(['activo' => true]);
        $developer->assignRole('Desarrollador');
        $requester = User::factory()->create(['activo' => true]);
        $requester->assignRole('Trabajador');

        app(CreateSupportTicketAction::class)->handle([
            'category' => SupportTicket::CategoryAccess,
            'subject' => 'No puedo entrar',
            'description' => 'El sistema rechaza mis credenciales de acceso.',
        ], $requester);

        Notification::assertSentTo(
            $developer,
            SupportTicketCreatedNotification::class,
            fn (SupportTicketCreatedNotification $notification): bool => $notification->afterCommit === true
        );
        Notification::assertSentTo(
            $requester,
            SupportTicketRequesterNotification::class,
            fn (SupportTicketRequesterNotification $notification): bool => $notification->afterCommit === true
        );
    }
}
