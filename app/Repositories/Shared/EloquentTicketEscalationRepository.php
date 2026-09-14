<?php

namespace App\Repositories\Shared;

use App\Models\TicketEscalation;
use Illuminate\Database\Eloquent\Collection;

class EloquentTicketEscalationRepository implements TicketEscalationRepositoryInterface
{
    public function create(array $attributes): TicketEscalation
    {
        return TicketEscalation::create($attributes);
    }

    public function getByTicket(int $ticketId): Collection
    {
        return TicketEscalation::with('escalator')
            ->where('ticket_id', $ticketId)
            ->latest('id')
            ->get();
    }
}
