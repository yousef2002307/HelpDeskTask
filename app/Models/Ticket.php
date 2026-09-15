<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'status',
        'previous_status',
        'priority',
        'customer_id',
        'agent_id',
        'escalated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'previous_status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'escalated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(TicketEscalation::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function isEscalatable(): bool
    {
        return $this->status->isEscalatable();
    }

    public function isDeescalatable(): bool
    {
        return $this->status->isDeescalatable();
    }
}
