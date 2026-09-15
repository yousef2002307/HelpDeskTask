<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(TicketStatus::cases())->value,
            'priority' => fake()->randomElement(TicketPriority::cases())->value,
            'customer_id' => Customer::factory(),
            'agent_id' => null,
            'escalated_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => TicketStatus::Open->value, 'escalated_at' => null]);
    }

    public function escalated(): static
    {
        return $this->state(['status' => TicketStatus::Escalated->value, 'escalated_at' => now()]);
    }

    public function closed(): static
    {
        return $this->state(['status' => TicketStatus::Closed->value, 'escalated_at' => null]);
    }

    public function withAgent(): static
    {
        return $this->state(['agent_id' => User::factory()]);
    }
}
