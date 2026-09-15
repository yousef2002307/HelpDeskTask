<?php

namespace App\Services\Shared;

use App\DTOs\EscalateTicketDTO;
use App\Models\Ticket;
use App\Services\Shared\Notification\NotificationResult;

interface TicketEscalationServiceInterface
{
    public function escalate(EscalateTicketDTO $dto): Ticket;

    public function deescalate(\App\DTOs\DeescalateTicketDTO $dto): Ticket;

    /**
     * @return array<string, NotificationResult>
     */
    public function sendNotifications(Ticket $ticket): array;
}
