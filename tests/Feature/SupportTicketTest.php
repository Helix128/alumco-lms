<?php

namespace Tests\Feature;

use App\Livewire\Support\CreateTicket;
use App\Livewire\Support\ManageTickets;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Notifications\SupportTicketCreatedNotification;
use App\Notifications\SupportTicketRequesterNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        RateLimiter::clear('support-ticket:127.0.0.1:ana@example.com');
    }

    public function test_guest_can_create_support_ticket_and_developers_are_notified(): void
    {
        Notification::fake();
        Storage::fake('local');
        $developer = $this->userWithRole('Desarrollador', ['activo' => true]);

        Livewire::test(CreateTicket::class)
            ->set('contact_name', 'Ana Prueba')
            ->set('contact_email', 'ana@example.com')
            ->set('category', SupportTicket::CategoryAccess)
            ->set('subject', 'No puedo ingresar')
            ->set('description', 'El sistema rechaza mi contraseña al intentar entrar.')
            ->set('attachments', [UploadedFile::fake()->image('captura.png')])
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', 'Ticket #1 enviado. El equipo técnico lo revisará.');

        $ticket = SupportTicket::first();

        $this->assertNotNull($ticket);
        $this->assertNull($ticket->requester_user_id);
        $this->assertSame('ana@example.com', $ticket->contact_email);
        $this->assertSame(SupportTicket::PriorityMedium, $ticket->priority);
        $this->assertSame(1, $ticket->attachments()->count());
        Storage::disk('local')->assertExists($ticket->attachments()->first()->path);
        Notification::assertSentTo($developer, SupportTicketCreatedNotification::class);
        Notification::assertSentOnDemand(
            SupportTicketRequesterNotification::class,
            function (SupportTicketRequesterNotification $notification, array $channels, object $notifiable): bool {
                return $notification->type === 'created'
                    && in_array('mail', $channels, true)
                    && $notifiable->routes['mail'] === 'ana@example.com';
            }
        );
    }

    public function test_guest_ticket_requires_valid_contact_and_description(): void
    {
        Livewire::test(CreateTicket::class)
            ->set('contact_name', 'A')
            ->set('contact_email', 'correo-invalido')
            ->set('category', SupportTicket::CategoryAccess)
            ->set('subject', 'Corto')
            ->set('description', 'Muy corto')
            ->call('submit')
            ->assertHasErrors(['contact_name', 'contact_email', 'description']);
    }

    public function test_public_creation_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            Livewire::test(CreateTicket::class)
                ->set('contact_name', 'Ana Prueba')
                ->set('contact_email', 'ana@example.com')
                ->set('category', SupportTicket::CategoryAccess)
                ->set('subject', 'No puedo ingresar')
                ->set('description', 'El sistema rechaza mi contraseña al intentar entrar.')
                ->call('submit');
        }

        Livewire::test(CreateTicket::class)
            ->set('contact_name', 'Ana Prueba')
            ->set('contact_email', 'ana@example.com')
            ->set('category', SupportTicket::CategoryAccess)
            ->set('subject', 'No puedo ingresar')
            ->set('description', 'El sistema rechaza mi contraseña al intentar entrar.')
            ->call('submit')
            ->assertHasErrors(['contact_email']);
    }

    public function test_support_route_names_are_unique(): void
    {
        $duplicateRouteNames = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter(fn (?string $name): bool => $name !== null && str_starts_with($name, 'support.'))
            ->countBy()
            ->filter(fn (int $count): bool => $count > 1);

        $this->assertSame([], $duplicateRouteNames->all());
    }

    public function test_public_support_route_uses_canonical_contact_path(): void
    {
        $this->assertSame(url('/soporte/contacto'), route('support.public.create'));

        $this->get('/soporte/contacto')->assertOk();
        $this->get('/soporte-publico')->assertRedirect('/soporte/contacto');
    }

    public function test_authenticated_user_creates_ticket_associated_to_account(): void
    {
        Notification::fake();
        $user = $this->userWithRole('Trabajador', ['activo' => true]);

        Livewire::actingAs($user)
            ->test(CreateTicket::class)
            ->set('category', SupportTicket::CategoryPlatformError)
            ->set('subject', 'Error en curso')
            ->set('description', 'La pantalla del curso queda en blanco al avanzar.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_tickets', [
            'requester_user_id' => $user->id,
            'contact_email' => $user->email,
            'subject' => 'Error en curso',
        ]);
    }

    public function test_authenticated_user_only_sees_own_ticket(): void
    {
        $owner = $this->userWithRole('Trabajador');
        $other = $this->userWithRole('Trabajador');
        $ticket = SupportTicket::factory()->for($owner, 'requester')->create();

        $this->actingAs($owner)->get(route('support.show', $ticket))->assertOk();
        $this->actingAs($other)->get(route('support.show', $ticket))->assertForbidden();
    }

    public function test_legacy_authenticated_support_ticket_url_redirects_to_canonical_route(): void
    {
        $owner = $this->userWithRole('Trabajador');
        $ticket = SupportTicket::factory()->for($owner, 'requester')->create();

        $this->actingAs($owner)
            ->get("/soporte/{$ticket->id}")
            ->assertRedirect(route('support.show', $ticket));
    }

    public function test_admin_without_developer_role_cannot_open_support_panel(): void
    {
        $admin = $this->userWithRole('Administrador');

        $this->actingAs($admin)
            ->get(route('dev.support.index'))
            ->assertForbidden();
    }

    public function test_developer_can_open_support_panel_without_server_error(): void
    {
        $developer = $this->userWithRole('Desarrollador', ['activo' => true]);

        $this->actingAs($developer)
            ->get(route('dev.support.index'))
            ->assertOk()
            ->assertSee('Centro de Soporte');
    }

    public function test_developer_can_manage_ticket_and_notify_requester(): void
    {
        Notification::fake();
        $developer = $this->userWithRole('Desarrollador', ['activo' => true]);
        $requester = $this->userWithRole('Trabajador', ['activo' => true]);
        $ticket = SupportTicket::factory()->for($requester, 'requester')->create([
            'subject' => 'Error de acceso',
            'priority' => SupportTicket::PriorityMedium,
            'status' => SupportTicket::StatusNew,
        ]);

        Livewire::actingAs($developer)
            ->test(ManageTickets::class)
            ->call('selectTicket', $ticket->id)
            ->call('assignToMe')
            ->set('newPriority', SupportTicket::PriorityCritical)
            ->set('newStatus', SupportTicket::StatusInReview)
            ->call('updateTicket')
            ->set('replyBody', 'Revisamos tu caso y necesitamos una nueva prueba.')
            ->call('reply')
            ->set('newStatus', SupportTicket::StatusResolved)
            ->set('newPriority', SupportTicket::PriorityCritical)
            ->call('updateTicket')
            ->call('closeTicket')
            ->assertHasNoErrors();

        $ticket->refresh();

        $this->assertSame($developer->id, $ticket->assigned_to_id);
        $this->assertSame(SupportTicket::StatusClosed, $ticket->status);
        $this->assertSame(SupportTicket::PriorityCritical, $ticket->priority);
        $this->assertSame(1, $ticket->messages()->count());
        Notification::assertSentTo($requester, SupportTicketRequesterNotification::class);
    }

    public function test_moving_ticket_to_waiting_user_notifies_requester(): void
    {
        Notification::fake();
        $developer = $this->userWithRole('Desarrollador', ['activo' => true]);
        $requester = $this->userWithRole('Trabajador', ['activo' => true]);
        $ticket = SupportTicket::factory()->for($requester, 'requester')->create([
            'status' => SupportTicket::StatusInReview,
        ]);

        Livewire::actingAs($developer)
            ->test(ManageTickets::class)
            ->call('selectTicket', $ticket->id)
            ->set('newStatus', SupportTicket::StatusWaitingUser)
            ->set('newPriority', SupportTicket::PriorityMedium)
            ->call('updateTicket')
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $requester,
            SupportTicketRequesterNotification::class,
            fn (SupportTicketRequesterNotification $notification): bool => $notification->type === 'waiting_user'
        );
    }

    public function test_manage_tickets_renders_without_selected_ticket(): void
    {
        $developer = $this->userWithRole('Desarrollador', ['activo' => true]);

        Livewire::actingAs($developer)
            ->test(ManageTickets::class)
            ->assertOk()
            ->assertSee('Bandeja de Entrada');
    }

    public function test_manage_tickets_renders_selected_ticket_details(): void
    {
        $developer = $this->userWithRole('Desarrollador', ['activo' => true]);
        $requester = $this->userWithRole('Trabajador', ['activo' => true]);
        $ticket = SupportTicket::factory()->for($requester, 'requester')->create([
            'subject' => 'Error con certificado',
        ]);

        Livewire::actingAs($developer)
            ->test(ManageTickets::class)
            ->call('selectTicket', $ticket->id)
            ->assertOk()
            ->assertSee('Error con certificado')
            ->assertSee($requester->name);
    }

    public function test_private_attachments_require_ticket_permission(): void
    {
        Storage::fake('local');
        $owner = $this->userWithRole('Trabajador');
        $other = $this->userWithRole('Trabajador');
        $ticket = SupportTicket::factory()->for($owner, 'requester')->create();
        Storage::disk('local')->put('support-tickets/1/captura.png', 'contenido');
        $attachment = SupportTicketAttachment::create([
            'support_ticket_id' => $ticket->id,
            'path' => 'support-tickets/1/captura.png',
            'original_name' => 'captura.png',
            'mime' => 'image/png',
            'size' => 9,
        ]);

        $this->actingAs($other)
            ->get(route('support.attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('support.attachments.download', $attachment))
            ->assertOk();
    }

    public function test_requester_cannot_download_internal_message_attachment(): void
    {
        Storage::fake('local');
        $developer = $this->userWithRole('Desarrollador');
        $requester = $this->userWithRole('Trabajador');
        $ticket = SupportTicket::factory()->for($requester, 'requester')->create();
        $message = SupportTicketMessage::factory()->for($ticket, 'ticket')->for($developer, 'author')->create([
            'is_internal' => true,
        ]);

        Storage::disk('local')->put('support-tickets/1/internal.png', 'contenido interno');
        $attachment = SupportTicketAttachment::create([
            'support_ticket_id' => $ticket->id,
            'support_ticket_message_id' => $message->id,
            'path' => 'support-tickets/1/internal.png',
            'original_name' => 'internal.png',
            'mime' => 'image/png',
            'size' => 17,
        ]);

        $this->actingAs($requester)
            ->get(route('support.attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($developer)
            ->get(route('support.attachments.download', $attachment))
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
