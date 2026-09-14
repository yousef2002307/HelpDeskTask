<?php

namespace App\Contracts;

use App\Models\Ticket;
use App\Services\Shared\Notification\NotificationResult;

interface NotificationChannelInterface
{
    public function name(): string;

    public function send(Ticket $ticket, int $attempt = 1): NotificationResult;
}
