<?php

namespace Tests\Feature;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Jobs\SendEscalationNotification;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketEscalationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_successfully_escalate_an_open_ticket(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => TicketStatus::Open,
            'escalated_at' => null,
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/escalate", [
            'reason' => 'Customer SLA breached, priority high',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'message' => 'Ticket escalated successfully. Notifications dispatched.',
            ])
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.status', TicketStatus::Escalated->value);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Escalated->value,
        ]);

        $this->assertNotNull($ticket->fresh()->escalated_at);

        $this->assertDatabaseHas('ticket_escalations', [
            'ticket_id' => $ticket->id,
            'reason' => 'Customer SLA breached, priority high',
        ]);

        Queue::assertPushed(SendEscalationNotification::class, 2);
    }

    public function test_escalation_records_escalated_by_agent_when_provided(): void
    {
        Queue::fake();

        $agent = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/escalate", [
            'escalated_by' => $agent->id,
            'reason' => 'Escalated by supervisor',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ticket_escalations', [
            'ticket_id' => $ticket->id,
            'escalated_by' => $agent->id,
            'reason' => 'Escalated by supervisor',
        ]);
    }

    public function test_escalation_automatically_attributes_authenticated_sanctum_user_when_logged_in(): void
    {
        Queue::fake();

        $agent = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        // Request made as authenticated user without specifying escalated_by
        $response = $this->actingAs($agent)
            ->postJson("/api/tickets/{$ticket->id}/escalate", [
                'reason' => 'Escalated by logged in supervisor',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ticket_escalations', [
            'ticket_id' => $ticket->id,
            'escalated_by' => $agent->id,
            'reason' => 'Escalated by logged in supervisor',
        ]);
    }

    public function test_returns_404_when_escalating_non_existent_ticket(): void
    {
        $response = $this->postJson('/api/tickets/999999/escalate', []);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status' => 404,
            ]);
    }

    #[DataProvider('unescalatableStatusProvider')]
    public function test_cannot_escalate_ticket_in_non_escalatable_status(TicketStatus $status): void
    {
        Queue::fake();

        $ticket = Ticket::factory()->create([
            'status' => $status,
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/escalate", [
            'reason' => 'Attempting invalid escalation',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status' => 422,
            ]);

        Queue::assertNothingPushed();
    }

    public static function unescalatableStatusProvider(): array
    {
        return [
            'Already Escalated' => [TicketStatus::Escalated],
            'Resolved Ticket' => [TicketStatus::Resolved],
            'Closed Ticket' => [TicketStatus::Closed],
        ];
    }

    #[DataProvider('escalatableStatusProvider')]
    public function test_can_escalate_tickets_in_valid_statuses(TicketStatus $status): void
    {
        Queue::fake();

        $ticket = Ticket::factory()->create(['status' => $status]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/escalate");

        $response->assertStatus(200);
        $this->assertSame(TicketStatus::Escalated, $ticket->fresh()->status);
    }

    public static function escalatableStatusProvider(): array
    {
        return [
            'Open Ticket' => [TicketStatus::Open],
            'In Progress Ticket' => [TicketStatus::InProgress],
        ];
    }

    public function test_validation_fails_for_invalid_escalated_by_id(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/escalate", [
            'escalated_by' => 99999,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_fetch_tickets_via_api_index(): void
    {
        Ticket::factory()->count(3)->create();

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'subject',
                        'status',
                        'priority',
                        'customer',
                    ],
                ],
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_can_fetch_single_ticket_via_api_show(): void
    {
        $ticket = Ticket::factory()->create([
            'subject' => 'Database Deadlock Investigation',
            'priority' => TicketPriority::High,
        ]);

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.subject', 'Database Deadlock Investigation')
            ->assertJsonPath('data.priority', 'high');
    }
}
