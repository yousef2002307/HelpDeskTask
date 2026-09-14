<?php

namespace App\Repositories\Shared;

use App\Enums\NotificationStatus;
use App\Models\NotificationLog;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentNotificationLogRepository implements NotificationLogRepositoryInterface
{
    public function log(
        int $ticketId,
        string $channel,
        string $recipient,
        int $attempt,
        NotificationStatus $status,
        ?string $errorMessage = null,
        ?DateTimeInterface $sentAt = null,
    ): NotificationLog {
        return NotificationLog::create([
            'ticket_id' => $ticketId,
            'channel' => $channel,
            'recipient' => $recipient,
            'attempt' => $attempt,
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => $sentAt,
        ]);
    }

    public function getByTicket(int $ticketId): Collection
    {
        return NotificationLog::where('ticket_id', $ticketId)
            ->latest('id')
            ->get();
    }

    public function getLatestForTicketAndChannel(int $ticketId, string $channel): ?NotificationLog
    {
        return NotificationLog::where('ticket_id', $ticketId)
            ->where('channel', $channel)
            ->latest('id')
            ->first();
    }
}
