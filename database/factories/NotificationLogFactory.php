<?php

namespace Database\Factories;

use App\Enums\NotificationStatus;
use App\Models\NotificationLog;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationLog>
 */
class NotificationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory()->open(),
            'channel' => fake()->randomElement(['email', 'slack']),
            'recipient' => fake()->safeEmail(),
            'attempt' => 1,
            'status' => NotificationStatus::Pending->value,
            'error_message' => null,
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => NotificationStatus::Sent->value, 'sent_at' => now()]);
    }

    public function failed(): static
    {
        return $this->state(['status' => NotificationStatus::Failed->value, 'error_message' => 'Connection timeout']);
    }

    public function exhausted(): static
    {
        return $this->state([
            'status' => NotificationStatus::Exhausted->value,
            'attempt' => 3,
            'error_message' => 'Max retries reached',
        ]);
    }
}
