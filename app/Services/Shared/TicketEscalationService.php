<?php

namespace App\Services\Shared;

use App\Contracts\NotificationChannelManagerInterface;
use App\DTOs\EscalateTicketDTO;
use App\Enums\NotificationStatus;
use App\Enums\TicketStatus;
use App\Jobs\SendEscalationNotification;
use App\Models\Ticket;
use App\Repositories\Shared\NotificationLogRepositoryInterface;
use App\Repositories\Shared\TicketEscalationRepositoryInterface;
use App\Repositories\Shared\TicketRepositoryInterface;
use App\Services\Shared\Notification\NotificationResult;
use DomainException;
use Illuminate\Support\Facades\DB;

class TicketEscalationService implements TicketEscalationServiceInterface
{
    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly TicketEscalationRepositoryInterface $escalationRepository,
        private readonly NotificationChannelManagerInterface $channelManager,
        private readonly NotificationLogRepositoryInterface $logRepository,
    ) {}

    public function escalate(EscalateTicketDTO $dto): Ticket
    {
        $ticket = $this->ticketRepository->findOrFail($dto->ticketId);

        if (! $ticket->isEscalatable()) {
            throw new DomainException(sprintf(
                'Ticket #%d cannot be escalated because its status is \'%s\'. Only open or in-progress tickets may be escalated.',
                $ticket->id,
                $ticket->status->value,
            ));
        }

        DB::transaction(function () use ($ticket, $dto): void {
            $this->ticketRepository->updateStatus(
                ticket: $ticket,
                status: TicketStatus::Escalated,
                escalatedAt: now(),
            );

            $this->escalationRepository->create([
                'ticket_id' => $ticket->id,
                'escalated_by' => $dto->escalatedBy,
                'reason' => $dto->reason,
            ]);
        });

        // Dispatch background notification jobs for all configured channels
        foreach ($this->channelManager->channels() as $channel) {
            SendEscalationNotification::dispatch($ticket->id, $channel->name());
        }

        return $this->ticketRepository->findOrFail($ticket->id);
    }

    /**
     * @return array<string, NotificationResult>
     */
    public function sendNotifications(Ticket $ticket): array
    {
        $results = [];

        foreach ($this->channelManager->channels() as $name => $channel) {
            $recipient = method_exists($channel, 'recipient') ? $channel->recipient($ticket) : 'system';
            $result = $channel->send($ticket, 1);
            $results[$name] = $result;

            $this->logRepository->log(
                ticketId: $ticket->id,
                channel: $name,
                recipient: $recipient,
                attempt: 1,
                status: $result->success ? NotificationStatus::Sent : NotificationStatus::Failed,
                errorMessage: $result->errorMessage,
                sentAt: $result->success ? now() : null,
            );
        }

        return $results;
    }
}
