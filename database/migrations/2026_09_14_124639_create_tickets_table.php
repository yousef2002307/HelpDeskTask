<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open');
            $table->string('priority', 20)->default('medium');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('escalated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
