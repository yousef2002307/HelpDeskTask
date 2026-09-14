<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('channel', 50);
            $table->string('recipient');
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'channel']);
            $table->index(['status', 'attempt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
