<?php

namespace App\Repositories\Shared;

use App\Enums\NotificationStatus;
use App\Models\NotificationLog;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

interface NotificationLogRepositoryInterface
{
    public function log(
        int $ticketId,
        string $channel,
        string $recipient,
        int $attempt,
        NotificationStatus $status,
        ?string $errorMessage = null,
        ?DateTimeInterface $sentAt = null,
    ): NotificationLog;

    public function getByTicket(int $ticketId): Collection;

    public function getLatestForTicketAndChannel(int $ticketId, string $channel): ?NotificationLog;
}
