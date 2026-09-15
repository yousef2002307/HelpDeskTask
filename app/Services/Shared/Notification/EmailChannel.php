<?php

namespace App\Services\Shared\Notification;

use App\Contracts\NotificationChannelInterface;
use App\Mail\TicketEscalatedMail;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailChannel implements NotificationChannelInterface
{
    public function name(): string
    {
        return 'email';
    }

    public function recipient(Ticket $ticket): string
    {
        return $ticket->agent?->email
            ?? config('escalation.channels.email.default_recipient', 'escalations@helpdesk.florgics.com');
    }

    public function send(Ticket $ticket, int $attempt = 1, array $context = []): NotificationResult
    {
        $recipient = $this->recipient($ticket);

        try {
            $isDeescalation = ($context['event'] ?? 'escalated') === 'deescalated';
            $mailable = $isDeescalation
                ? new \App\Mail\TicketDeescalatedMail($ticket, $context['reason'] ?? null)
                : new TicketEscalatedMail($ticket);

            Mail::to($recipient)->send($mailable);

            return NotificationResult::success($this->name());
        } catch (Throwable $e) {
            Log::warning(sprintf('Email notification failed for Ticket #%d on attempt %d: %s', $ticket->id, $attempt, $e->getMessage()));

            return NotificationResult::failure($this->name(), $e->getMessage());
        }
    }
}
