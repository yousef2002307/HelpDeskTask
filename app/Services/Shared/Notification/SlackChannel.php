<?php

namespace App\Services\Shared\Notification;

use App\Contracts\NotificationChannelInterface;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SlackChannel implements NotificationChannelInterface
{
    public function name(): string
    {
        return 'slack';
    }

    public function recipient(Ticket $ticket): string
    {
        return config('escalation.channels.slack.channel', '#ticket-escalations');
    }

    public function send(Ticket $ticket, int $attempt = 1): NotificationResult
    {
        $webhookUrl = config('escalation.channels.slack.webhook_url');

        if (empty($webhookUrl)) {
            return NotificationResult::failure($this->name(), 'Slack webhook URL is not configured.');
        }

        try {
            $payload = [
                'text' => sprintf(':rotating_light: *Ticket Escalated: #%d - %s*', $ticket->id, $ticket->subject),
                'blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => sprintf('🚨 Ticket Escalated: #%d', $ticket->id),
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            ['type' => 'mrkdwn', 'text' => sprintf('*Subject:*\n%s', $ticket->subject)],
                            ['type' => 'mrkdwn', 'text' => sprintf('*Priority:*\n%s', strtoupper($ticket->priority->value))],
                            ['type' => 'mrkdwn', 'text' => sprintf('*Customer:*\n%s', $ticket->customer->name)],
                            ['type' => 'mrkdwn', 'text' => sprintf('*Status:*\n%s', strtoupper($ticket->status->value))],
                        ],
                    ],
                ],
            ];

            $response = Http::timeout(5)->post($webhookUrl, $payload);

            if ($response->successful()) {
                return NotificationResult::success($this->name());
            }

            $error = sprintf('Slack webhook returned HTTP %d: %s', $response->status(), $response->body());
            Log::warning(sprintf('Slack notification failed for Ticket #%d: %s', $ticket->id, $error));

            return NotificationResult::failure($this->name(), $error);
        } catch (Throwable $e) {
            Log::warning(sprintf('Slack notification exception for Ticket #%d on attempt %d: %s', $ticket->id, $attempt, $e->getMessage()));

            return NotificationResult::failure($this->name(), $e->getMessage());
        }
    }
}
