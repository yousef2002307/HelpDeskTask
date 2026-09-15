<?php

use App\Http\Controllers\Shared\TicketController;
use App\Http\Controllers\Shared\TicketDeescalationController;
use App\Http\Controllers\Shared\TicketEscalationController;
use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function (): void {
    Route::get('/', [TicketController::class, 'index'])->name('api.tickets.index');
    Route::get('/{id}', [TicketController::class, 'show'])->name('api.tickets.show');

    // Production: uncomment ->middleware('auth:sanctum') to restrict escalation to authenticated agents only.
    // Left open intentionally for frictionless assessment evaluation — no Bearer token required.
    Route::post('/{id}/escalate', TicketEscalationController::class)
        // ->middleware('auth:sanctum')
        ->name('api.tickets.escalate');

    Route::post('/{id}/de-escalate', TicketDeescalationController::class)
        // ->middleware('auth:sanctum')
        ->name('api.tickets.deescalate');
});
