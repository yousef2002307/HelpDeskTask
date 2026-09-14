<?php

namespace App\Http\Resources\Shared;

use App\Models\TicketEscalation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketEscalation
 */
class EscalationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'escalated_by' => $this->escalated_by,
            'escalator_name' => $this->escalator?->name,
            'reason' => $this->reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
