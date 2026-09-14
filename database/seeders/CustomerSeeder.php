<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->create([
            'name' => 'Acme Corporation',
            'email' => 'support@acme.corp',
            'phone' => '+15551234567',
        ]);

        Customer::factory()->create([
            'name' => 'Starlight Media',
            'email' => 'ops@starlightmedia.io',
            'phone' => '+15559876543',
        ]);

        Customer::factory()->count(5)->create();
    }
}
