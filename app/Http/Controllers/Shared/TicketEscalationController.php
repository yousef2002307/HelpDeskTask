<?php

namespace App\Http\Controllers\Shared;

use App\DTOs\EscalateTicketDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\EscalateTicketRequest;
use App\Http\Resources\Shared\TicketResource;
use App\Services\Shared\TicketEscalationServiceInterface;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use YousefAhmedAbdalgawad\ApiResponder\Traits\ApiResponser;

/**
 * @group Ticket Escalation
 *
 * APIs for managing ticket escalation workflows and triggering incident alerts.
 */
class TicketEscalationController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly TicketEscalationServiceInterface $escalationService,
    ) {}

    /**
     * Escalate a Ticket
     *
     * Escalates an open or in-progress ticket, sets its status to "escalated",
     * records the escalation timestamp and reason, and automatically dispatches
     * multi-channel notifications (Email & Slack) with up to 3 automatic retries.
     *
     * If an authenticated user is present (e.g. via Sanctum), they are automatically
     * recorded as the escalator. Otherwise, `escalated_by` can be optionally supplied.
     *
     * @urlParam id integer required The ID of the ticket to escalate. Example: 1
     *
     * @response 200 scenario="Successful escalation" {
     *   "success": true,
     *   "status": 200,
     *   "message": "Ticket escalated successfully. Notifications dispatched.",
     *   "data": {
     *     "id": 1,
     *     "subject": "Payment Gateway Returning 502 Bad Gateway",
     *     "description": "Transactions are dropping during peak hours.",
     *     "status": "escalated",
     *     "status_label": "Escalated",
     *     "priority": "urgent",
     *     "priority_label": "Urgent",
     *     "is_escalatable": false,
     *     "customer": {
     *       "id": 1,
     *       "name": "Acme Corporation",
     *       "email": "support@acme.corp"
     *     },
     *     "escalated_at": "2026-09-14T15:10:00.000000Z"
     *   }
     * }
     * @response 422 scenario="Ticket is already escalated, closed, or resolved" {
     *   "success": false,
     *   "status": 422,
     *   "message": "Ticket #1 cannot be escalated because its status is 'escalated'. Only open or in-progress tickets may be escalated."
     * }
     * @response 404 scenario="Ticket not found" {
     *   "success": false,
     *   "status": 404,
     *   "message": "Ticket #999999 not found."
     * }
     */
    public function __invoke(EscalateTicketRequest $request, int $id): Response
    {
        $validated = $request->validated();

        // Auth-aware attribution: prioritize authenticated user if present
        if ($request->user() && empty($validated['escalated_by'])) {
            $validated['escalated_by'] = $request->user()->id;
        }

        $dto = EscalateTicketDTO::fromArray($id, $validated);

        try {
            $ticket = $this->escalationService->escalate($dto);

            return $this->successResponse(
                new TicketResource($ticket),
                'Ticket escalated successfully. Notifications dispatched.',
                200,
            );
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse(sprintf('Ticket #%d not found.', $id));
        } catch (DomainException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: 422,
            );
        }
    }
}
