<?php

namespace App\Http\Controllers\Shared;

use App\DTOs\DeescalateTicketDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\DeescalateTicketRequest;
use App\Http\Resources\Shared\TicketResource;
use App\Services\Shared\TicketEscalationServiceInterface;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use YousefAhmedAbdalgawad\ApiResponder\Traits\ApiResponser;

/**
 * @group Ticket Escalation
 *
 * APIs for managing ticket escalation and de-escalation workflows.
 */
class TicketDeescalationController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly TicketEscalationServiceInterface $escalationService,
    ) {}

    /**
     * De-escalate a Ticket
     *
     * De-escalates an escalated ticket back to its previous status (e.g. open or in_progress),
     * clears the escalation timestamp, records an audit log entry, and automatically
     * dispatches de-escalation notifications (Email & Slack) with automatic retries.
     *
     * @urlParam id integer required The ID of the ticket to de-escalate. Example: 1
     *
     * @response 200 scenario="Successful de-escalation" {
     *   "success": true,
     *   "status": 200,
     *   "message": "Ticket de-escalated successfully. Notifications dispatched.",
     *   "data": {
     *     "id": 1,
     *     "subject": "Payment Gateway Returning 502 Bad Gateway",
     *     "status": "open",
     *     "status_label": "Open",
     *     "priority": "urgent",
     *     "priority_label": "Urgent",
     *     "is_escalatable": true,
     *     "is_deescalatable": false,
     *     "customer": {
     *       "id": 1,
     *       "name": "Acme Corporation",
     *       "email": "support@acme.corp"
     *     },
     *     "escalated_at": null
     *   }
     * }
     * @response 422 scenario="Ticket is not in escalated status" {
     *   "success": false,
     *   "status": 422,
     *   "message": "Ticket #1 cannot be de-escalated because its status is 'open'. Only escalated tickets may be de-escalated."
     * }
     * @response 404 scenario="Ticket not found" {
     *   "success": false,
     *   "status": 404,
     *   "message": "Ticket #999999 not found."
     * }
     */
    public function __invoke(DeescalateTicketRequest $request, int $id): Response
    {
        $validated = $request->validated();

        // Auth-aware attribution: prioritize authenticated user if present
        if ($request->user() && empty($validated['deescalated_by'])) {
            $validated['deescalated_by'] = $request->user()->id;
        }

        $dto = DeescalateTicketDTO::fromArray($id, $validated);

        try {
            $ticket = $this->escalationService->deescalate($dto);
            $ticket->load(['escalations', 'notificationLogs']);

            return $this->successResponse(
                new TicketResource($ticket),
                'Ticket de-escalated successfully. Notifications dispatched.',
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
