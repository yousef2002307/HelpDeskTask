<?php

namespace App\Repositories\Shared;

use App\Models\TicketEscalation;
use Illuminate\Database\Eloquent\Collection;

interface TicketEscalationRepositoryInterface
{
    public function create(array $attributes): TicketEscalation;

    public function getByTicket(int $ticketId): Collection;
}
