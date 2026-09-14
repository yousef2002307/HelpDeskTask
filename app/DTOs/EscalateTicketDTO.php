<?php

namespace App\DTOs;

readonly class EscalateTicketDTO
{
    public function __construct(
        public int $ticketId,
        public ?int $escalatedBy = null,
        public ?string $reason = null,
    ) {}

    public static function fromArray(int $ticketId, array $data): self
    {
        return new self(
            ticketId: $ticketId,
            escalatedBy: $data['escalated_by'] ?? null,
            reason: $data['reason'] ?? null,
        );
    }
}
