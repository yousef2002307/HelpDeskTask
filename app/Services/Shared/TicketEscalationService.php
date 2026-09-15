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
                previousStatus: $ticket->status,
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

    public function deescalate(\App\DTOs\DeescalateTicketDTO $dto): Ticket
    {
        $ticket = $this->ticketRepository->findOrFail($dto->ticketId);

        if (! $ticket->isDeescalatable()) {
            throw new DomainException(sprintf(
                'Ticket #%d cannot be de-escalated because its status is \'%s\'. Only escalated tickets may be de-escalated.',
                $ticket->id,
                $ticket->status->value,
            ));
        }

        $targetStatus = $ticket->previous_status ?? TicketStatus::Open;

        DB::transaction(function () use ($ticket, $dto, $targetStatus): void {
            $this->ticketRepository->deescalate(
                ticket: $ticket,
                targetStatus: $targetStatus,
            );

            $reason = '[De-escalated] ' . ($dto->reason ?: 'Reverted to ' . $targetStatus->label());

            $this->escalationRepository->create([
                'ticket_id' => $ticket->id,
                'escalated_by' => $dto->deescalatedBy,
                'reason' => $reason,
            ]);
        });

        // Dispatch background de-escalation notification jobs for all configured channels
        foreach ($this->channelManager->channels() as $channel) {
            \App\Jobs\SendDeescalationNotification::dispatch($ticket->id, $channel->name(), $dto->reason);
        }

        return $this->ticketRepository->findOrFail($ticket->id);
    }

    /**
     * Executes all configured notification channels synchronously and persists delivery results.
     *
     * This is the synchronous counterpart to the async queue path in escalate(). It is intentionally
     * kept public so unit tests can drive channel dispatch and log assertions without a running queue
     * worker, while production always goes through SendEscalationNotification jobs.
     *
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
