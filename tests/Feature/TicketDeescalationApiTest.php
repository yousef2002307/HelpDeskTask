<?php

namespace Tests\Feature;

use App\Contracts\NotificationChannelInterface;
use App\Contracts\NotificationChannelManagerInterface;
use App\Enums\NotificationStatus;
use App\Enums\TicketStatus;
use App\Jobs\SendDeescalationNotification;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Shared\NotificationLogRepositoryInterface;
use App\Repositories\Shared\TicketRepositoryInterface;
use App\Services\Shared\Notification\NotificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketDeescalationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_successfully_deescalate_an_escalated_ticket_to_previous_status(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => TicketStatus::Escalated,
            'previous_status' => TicketStatus::InProgress,
            'escalated_at' => now()->subHours(2),
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/de-escalate", [
            'reason' => 'Issue verified fixed by senior DBA, reverting to normal queue',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'message' => 'Ticket de-escalated successfully. Notifications dispatched.',
            ])
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.status', TicketStatus::InProgress->value)
            ->assertJsonPath('data.is_escalatable', true)
            ->assertJsonPath('data.is_deescalatable', false);

        $freshTicket = $ticket->fresh();
        $this->assertSame(TicketStatus::InProgress, $freshTicket->status);
        $this->assertNull($freshTicket->escalated_at);

        $this->assertDatabaseHas('ticket_escalations', [
            'ticket_id' => $ticket->id,
            'reason' => '[De-escalated] Issue verified fixed by senior DBA, reverting to normal queue',
        ]);

        Queue::assertPushed(SendDeescalationNotification::class, 2);
    }

    public function test_deescalation_reverts_to_open_if_previous_status_was_open(): void
    {
        Queue::fake();

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Escalated,
            'previous_status' => TicketStatus::Open,
            'escalated_at' => now(),
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/de-escalate");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', TicketStatus::Open->value);

        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->escalated_at);
    }

    public function test_cannot_deescalate_ticket_not_in_escalated_status(): void
    {
        Queue::fake();

        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Open,
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/de-escalate", [
            'reason' => 'Attempting to deescalate open ticket',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status' => 422,
            ]);

        Queue::assertNothingPushed();
    }

    public function test_returns_404_when_deescalating_non_existent_ticket(): void
    {
        $response = $this->postJson('/api/tickets/999999/de-escalate');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status' => 404,
            ]);
    }

    public function test_deescalation_attributes_authenticated_sanctum_user_when_logged_in(): void
    {
        Queue::fake();

        $agent = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Escalated,
            'previous_status' => TicketStatus::Open,
        ]);

        $response = $this->actingAs($agent)
            ->postJson("/api/tickets/{$ticket->id}/de-escalate", [
                'reason' => 'Supervisor manual de-escalation',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ticket_escalations', [
            'ticket_id' => $ticket->id,
            'escalated_by' => $agent->id,
            'reason' => '[De-escalated] Supervisor manual de-escalation',
        ]);
    }

    public function test_send_deescalation_notification_job_logs_success(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Open,
            'previous_status' => TicketStatus::Open,
        ]);

        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('name')->willReturn('email');
        $channel->method('send')
            ->willReturn(NotificationResult::success('email'));

        $manager = $this->createMock(NotificationChannelManagerInterface::class);
        $manager->method('channel')->with('email')->willReturn($channel);

        $job = new SendDeescalationNotification($ticket->id, 'email', 'Fixed');

        $job->handle(
            app(TicketRepositoryInterface::class),
            $manager,
            app(NotificationLogRepositoryInterface::class),
        );

        $this->assertDatabaseHas('notification_logs', [
            'ticket_id' => $ticket->id,
            'channel' => 'email',
            'status' => NotificationStatus::Sent->value,
            'attempt' => 1,
        ]);
    }
}
