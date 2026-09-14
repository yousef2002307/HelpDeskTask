<?php

namespace Tests\Unit;

use App\Contracts\NotificationChannelInterface;
use App\Contracts\NotificationChannelManagerInterface;
use App\DTOs\EscalateTicketDTO;
use App\Enums\NotificationStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketEscalation;
use App\Repositories\Shared\NotificationLogRepositoryInterface;
use App\Repositories\Shared\TicketEscalationRepositoryInterface;
use App\Repositories\Shared\TicketRepositoryInterface;
use App\Services\Shared\Notification\NotificationResult;
use App\Services\Shared\TicketEscalationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketEscalationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_escalate_updates_status_records_escalation_and_dispatches_notifications(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'status' => TicketStatus::Open,
        ]);

        $ticketRepo = $this->createMock(TicketRepositoryInterface::class);
        $ticketRepo->expects($this->exactly(2))
            ->method('findOrFail')
            ->with($ticket->id)
            ->willReturn($ticket);

        $ticketRepo->expects($this->once())
            ->method('updateStatus')
            ->with($ticket, TicketStatus::Escalated, $this->isInstanceOf(\DateTimeInterface::class))
            ->willReturn(true);

        $escalationRepo = $this->createMock(TicketEscalationRepositoryInterface::class);
        $escalationRepo->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $attributes) use ($ticket) {
                return $attributes['ticket_id'] === $ticket->id
                    && $attributes['reason'] === 'Critical payment issue'
                    && $attributes['escalated_by'] === 42;
            }))
            ->willReturn(new TicketEscalation());

        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('name')->willReturn('slack');

        $channelManager = $this->createMock(NotificationChannelManagerInterface::class);
        $channelManager->method('channels')->willReturn(['slack' => $channel]);

        $logRepo = $this->createMock(NotificationLogRepositoryInterface::class);

        $service = new TicketEscalationService(
            ticketRepository: $ticketRepo,
            escalationRepository: $escalationRepo,
            channelManager: $channelManager,
            logRepository: $logRepo,
        );

        $dto = new EscalateTicketDTO(
            ticketId: $ticket->id,
            escalatedBy: 42,
            reason: 'Critical payment issue',
        );

        $result = $service->escalate($dto);

        $this->assertInstanceOf(Ticket::class, $result);
    }

    public function test_escalate_throws_domain_exception_when_ticket_is_not_escalatable(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Closed,
        ]);

        $ticketRepo = $this->createMock(TicketRepositoryInterface::class);
        $ticketRepo->method('findOrFail')->with($ticket->id)->willReturn($ticket);

        $escalationRepo = $this->createMock(TicketEscalationRepositoryInterface::class);
        $channelManager = $this->createMock(NotificationChannelManagerInterface::class);
        $logRepo = $this->createMock(NotificationLogRepositoryInterface::class);

        $service = new TicketEscalationService(
            ticketRepository: $ticketRepo,
            escalationRepository: $escalationRepo,
            channelManager: $channelManager,
            logRepository: $logRepo,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Ticket #{$ticket->id} cannot be escalated because its status is 'closed'");

        $dto = new EscalateTicketDTO(ticketId: $ticket->id);
        $service->escalate($dto);
    }

    public function test_send_notifications_executes_registered_channels_and_logs_results(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);

        $ticketRepo = $this->createMock(TicketRepositoryInterface::class);
        $escalationRepo = $this->createMock(TicketEscalationRepositoryInterface::class);

        $emailChannel = $this->createMock(NotificationChannelInterface::class);
        $emailChannel->method('name')->willReturn('email');
        $emailChannel->expects($this->once())
            ->method('send')
            ->with($ticket, 1)
            ->willReturn(NotificationResult::success('email'));

        $slackChannel = $this->createMock(NotificationChannelInterface::class);
        $slackChannel->method('name')->willReturn('slack');
        $slackChannel->expects($this->once())
            ->method('send')
            ->with($ticket, 1)
            ->willReturn(NotificationResult::failure('slack', 'Webhook offline'));

        $channelManager = $this->createMock(NotificationChannelManagerInterface::class);
        $channelManager->method('channels')->willReturn([
            'email' => $emailChannel,
            'slack' => $slackChannel,
        ]);

        $logRepo = $this->createMock(NotificationLogRepositoryInterface::class);
        $logRepo->expects($this->exactly(2))
            ->method('log')
            ->willReturnCallback(function (
                int $ticketId,
                string $channel,
                string $recipient,
                int $attempt,
                NotificationStatus $status,
                ?string $errorMessage = null,
                ?\DateTimeInterface $sentAt = null,
            ) use ($ticket) {
                $this->assertSame($ticket->id, $ticketId);
                $this->assertSame(1, $attempt);

                if ($channel === 'email') {
                    $this->assertSame(NotificationStatus::Sent, $status);
                    $this->assertNotNull($sentAt);
                } else {
                    $this->assertSame(NotificationStatus::Failed, $status);
                    $this->assertSame('Webhook offline', $errorMessage);
                }

                return new \App\Models\NotificationLog();
            });

        $service = new TicketEscalationService(
            ticketRepository: $ticketRepo,
            escalationRepository: $escalationRepo,
            channelManager: $channelManager,
            logRepository: $logRepo,
        );

        $results = $service->sendNotifications($ticket);

        $this->assertCount(2, $results);
        $this->assertTrue($results['email']->success);
        $this->assertFalse($results['slack']->success);
    }
}
