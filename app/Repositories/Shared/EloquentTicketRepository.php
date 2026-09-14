<?php

namespace App\Repositories\Shared;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function find(int $id): ?Ticket
    {
        return Ticket::with(['customer', 'agent', 'escalations', 'notificationLogs'])->find($id);
    }

    public function findOrFail(int $id): Ticket
    {
        return Ticket::with(['customer', 'agent', 'escalations', 'notificationLogs'])->findOrFail($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::with(['customer', 'agent'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Ticket::with(['customer', 'agent'])->latest('id')->get();
    }

    public function updateStatus(Ticket $ticket, TicketStatus $status, ?DateTimeInterface $escalatedAt = null): bool
    {
        $attributes = ['status' => $status];

        if ($escalatedAt !== null) {
            $attributes['escalated_at'] = $escalatedAt;
        }

        return $ticket->update($attributes);
    }
}
