<?php

namespace App\DTOs;

readonly class DeescalateTicketDTO
{
    public function __construct(
        public int $ticketId,
        public ?int $deescalatedBy = null,
        public ?string $reason = null,
    ) {}

    /**
     * @param  array{deescalated_by?: ?int, reason?: ?string}  $data
     */
    public static function fromArray(int $ticketId, array $data): self
    {
        return new self(
            ticketId: $ticketId,
            deescalatedBy: isset($data['deescalated_by']) ? (int) $data['deescalated_by'] : null,
            reason: $data['reason'] ?? null,
        );
    }
}
