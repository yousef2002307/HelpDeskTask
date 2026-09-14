<?php

use App\Http\Controllers\Shared\TicketController;
use App\Http\Controllers\Shared\TicketEscalationController;
use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function (): void {
    Route::get('/', [TicketController::class, 'index'])->name('api.tickets.index');
    Route::get('/{id}', [TicketController::class, 'show'])->name('api.tickets.show');
    Route::post('/{id}/escalate', TicketEscalationController::class)->name('api.tickets.escalate');
});
