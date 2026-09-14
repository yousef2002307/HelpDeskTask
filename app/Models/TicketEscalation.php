<?php

namespace App\Models;

use Database\Factories\TicketEscalationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEscalation extends Model
{
    /** @use HasFactory<TicketEscalationFactory> */
    use HasFactory;

    protected $fillable = ['ticket_id', 'escalated_by', 'reason'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }
}
