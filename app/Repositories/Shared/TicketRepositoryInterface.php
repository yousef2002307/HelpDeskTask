<?php

namespace App\Repositories\Shared;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TicketRepositoryInterface
{
    public function find(int $id): ?Ticket;

    public function findOrFail(int $id): Ticket;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function all(): Collection;

    public function updateStatus(Ticket $ticket, TicketStatus $status, ?DateTimeInterface $escalatedAt = null, ?TicketStatus $previousStatus = null): bool;

    public function deescalate(Ticket $ticket, TicketStatus $targetStatus): bool;
}
