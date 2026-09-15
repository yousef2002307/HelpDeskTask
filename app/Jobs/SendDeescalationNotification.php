<?php

namespace App\Jobs;

use App\Contracts\NotificationChannelManagerInterface;
use App\Enums\NotificationStatus;
use App\Repositories\Shared\NotificationLogRepositoryInterface;
use App\Repositories\Shared\TicketRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendDeescalationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 180];

    public function __construct(
        public int $ticketId,
        public string $channelName,
        public ?string $reason = null,
    ) {
        $this->tries = (int) config('escalation.max_retries', 3);
    }

    public function handle(
        TicketRepositoryInterface $ticketRepo,
        NotificationChannelManagerInterface $channelManager,
        NotificationLogRepositoryInterface $logRepo,
    ): void {
        $ticket = $ticketRepo->find($this->ticketId);

        if (! $ticket) {
            Log::error("SendDeescalationNotification: Ticket #{$this->ticketId} not found.");

            return;
        }

        $channel = $channelManager->channel($this->channelName);

        if (! $channel) {
            Log::error("SendDeescalationNotification: Channel '{$this->channelName}' not found.");

            return;
        }

        $currentAttempt = max(1, $this->attempts());
        $recipient = method_exists($channel, 'recipient') ? $channel->recipient($ticket) : 'system';

        $result = $channel->send($ticket, $currentAttempt, [
            'event' => 'deescalated',
            'reason' => $this->reason,
        ]);

        if ($result->success) {
            $logRepo->log(
                ticketId: $ticket->id,
                channel: $this->channelName,
                recipient: $recipient,
                attempt: $currentAttempt,
                status: NotificationStatus::Sent,
                sentAt: now(),
            );

            return;
        }

        $isExhausted = $currentAttempt >= $this->tries;
        $status = $isExhausted ? NotificationStatus::Exhausted : NotificationStatus::Failed;

        $logRepo->log(
            ticketId: $ticket->id,
            channel: $this->channelName,
            recipient: $recipient,
            attempt: $currentAttempt,
            status: $status,
            errorMessage: $result->errorMessage,
        );

        if (! $isExhausted) {
            throw new RuntimeException($result->errorMessage ?? "De-escalation notification via {$this->channelName} failed on attempt {$currentAttempt}.");
        }
    }

    public function failed(?Throwable $exception): void
    {
        try {
            $logRepo = app(NotificationLogRepositoryInterface::class);
            $ticketRepo = app(TicketRepositoryInterface::class);
            $ticket = $ticketRepo->find($this->ticketId);
            $channelManager = app(NotificationChannelManagerInterface::class);
            $channel = $channelManager->channel($this->channelName);

            $recipient = ($channel && $ticket && method_exists($channel, 'recipient'))
                ? $channel->recipient($ticket)
                : 'system';

            $logRepo->log(
                ticketId: $this->ticketId,
                channel: $this->channelName,
                recipient: $recipient,
                attempt: $this->tries,
                status: NotificationStatus::Exhausted,
                errorMessage: $exception?->getMessage() ?? 'Max retries exhausted.',
            );
        } catch (Throwable) {
            // Ignore secondary logging failures
        }
    }
}
