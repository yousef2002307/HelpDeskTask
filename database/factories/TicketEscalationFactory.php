<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketEscalation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketEscalation>
 */
class TicketEscalationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id'    => Ticket::factory()->open(),
            'escalated_by' => User::factory(),
            'reason'       => fake()->optional()->sentence(),
        ];
    }
}
