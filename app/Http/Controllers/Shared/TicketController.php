<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\TicketResource;
use App\Repositories\Shared\TicketRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;
use YousefAhmedAbdalgawad\ApiResponder\Traits\ApiResponser;

/**
 * @group Support Tickets
 *
 * APIs for querying support tickets and tracking incidents.
 */
class TicketController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository,
    ) {}

    /**
     * List Tickets
     *
     * Retrieves paginated support tickets along with customer and agent relationships.
     *
     * @response 200 scenario="Tickets list" {
     *   "success": true,
     *   "status": 200,
     *   "message": "Tickets retrieved successfully.",
     *   "data": [
     *     {
     *       "id": 1,
     *       "subject": "Payment Gateway Returning 502 Bad Gateway",
     *       "status": "open",
     *       "status_label": "Open",
     *       "priority": "urgent",
     *       "priority_label": "Urgent",
     *       "is_escalatable": true,
     *       "customer": {
     *         "id": 1,
     *         "name": "Acme Corporation",
     *         "email": "support@acme.corp"
     *       }
     *     }
     *   ],
     *   "pagination": {
     *     "total": 6,
     *     "per_page": 15,
     *     "current_page": 1,
     *     "last_page": 1
     *   }
     * }
     */
    public function index(Request $request): Response|InertiaResponse
    {
        $paginator = $this->ticketRepository->paginate(15);

        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->successResponse(
                TicketResource::collection($paginator->items()),
                'Tickets retrieved successfully.',
                200,
                [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            );
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => TicketResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Get Single Ticket
     *
     * Fetches detailed ticket records, including customer profile, assigned agent,
     * escalation history records, and notification delivery attempt logs.
     *
     * @urlParam id integer required The ID of the ticket. Example: 1
     *
     * @response 200 scenario="Ticket details" {
     *   "success": true,
     *   "status": 200,
     *   "message": "Ticket retrieved successfully.",
     *   "data": {
     *     "id": 1,
     *     "subject": "Payment Gateway Returning 502 Bad Gateway",
     *     "description": "Customers cannot checkout during peak traffic hours.",
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
     *     "escalations": [],
     *     "notification_logs": []
     *   }
     * }
     * @response 404 scenario="Ticket not found" {
     *   "success": false,
     *   "status": 404,
     *   "message": "Ticket #999999 not found."
     * }
     */
    public function show(Request $request, int $id): Response|InertiaResponse
    {
        $ticket = $this->ticketRepository->findOrFail($id);

        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->successResponse(
                new TicketResource($ticket),
                'Ticket retrieved successfully.',
                200,
            );
        }

        return Inertia::render('Tickets/Show', [
            'ticket' => (new TicketResource($ticket))->resolve(),
        ]);
    }
}
