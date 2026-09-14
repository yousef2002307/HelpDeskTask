<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $agents = User::all();

        if ($customers->isEmpty()) {
            $customers = Customer::factory()->count(3)->create();
        }

        if ($agents->isEmpty()) {
            $agents = User::factory()->count(2)->create();
        }

        // 1. High-priority open ticket ready for escalation demo
        Ticket::factory()->create([
            'customer_id' => $customers->first()->id,
            'agent_id' => $agents->first()->id,
            'subject' => 'Payment Gateway Returning 502 Bad Gateway',
            'description' => 'Customers cannot checkout during peak traffic hours. Transactions are dropping.',
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Urgent,
        ]);

        // 2. Medium open ticket
        Ticket::factory()->create([
            'customer_id' => $customers->first()->id,
            'agent_id' => null,
            'subject' => 'SSL Certificate Renewal Failure',
            'description' => 'Subdomain api.client.com SSL expired today. Urgent intervention needed.',
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::High,
        ]);

        // 3. In Progress ticket
        Ticket::factory()->create([
            'customer_id' => $customers->skip(1)->first()?->id ?? $customers->first()->id,
            'agent_id' => $agents->first()->id,
            'subject' => 'Slow Database Queries on Invoicing Module',
            'description' => 'Monthly billing report export takes more than 5 minutes to generate.',
            'status' => TicketStatus::InProgress,
            'priority' => TicketPriority::Medium,
        ]);

        // 4. Already escalated ticket
        Ticket::factory()->escalated()->create([
            'customer_id' => $customers->skip(1)->first()?->id ?? $customers->first()->id,
            'agent_id' => $agents->first()->id,
            'subject' => 'Data Center Outage in EU-West',
            'description' => 'Primary cluster lost network connectivity.',
            'priority' => TicketPriority::Urgent,
        ]);

        // 5. Resolved ticket
        Ticket::factory()->create([
            'customer_id' => $customers->first()->id,
            'agent_id' => $agents->first()->id,
            'subject' => 'User Password Reset Not Triggering',
            'description' => 'Resolved after clearing Redis cache keys.',
            'status' => TicketStatus::Resolved,
            'priority' => TicketPriority::Low,
        ]);

        // 6. Closed ticket
        Ticket::factory()->closed()->create([
            'customer_id' => $customers->first()->id,
            'agent_id' => $agents->first()->id,
            'subject' => 'Update billing address',
            'description' => 'Company changed legal domicile.',
            'priority' => TicketPriority::Low,
        ]);
    }
}
