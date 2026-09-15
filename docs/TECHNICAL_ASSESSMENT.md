# Flojics Technical Assessment: Ticket Escalation & Notification System

This document provides the complete, unified technical documentation required by the Flojics Technical Assessment, covering discovery questions, architectural decisions, database design, testing strategy, and self-testing notes.

---

## 1. Questions for the Product Owner (PO)

1. **Authorization & RBAC:** Can any authenticated user escalate a ticket, or is this permission strictly restricted to assigned agents and supervisors?
2. **Re-escalation Rules:** If a ticket is already in `Escalated` status, should subsequent escalation attempts be rejected with a validation error (`422 Unprocessable`), or should multi-tier escalation (Tier 1 ➔ Tier 2 ➔ Tier 3) be permitted?
3. **Escalation Reason / Notes:** Should the escalation action require an optional or mandatory comment/reason from the agent, or is it purely a one-click action?
4. **Recipient Target Configuration:**
   - **Email:** Should notifications target the assigned agent, the customer, or a configurable escalation email distribution list?
   - **Slack:** Should notifications post to a shared channel (e.g., `#urgent-escalations`) via webhook, or directly direct-message (DM) the agent?
5. **Dead-Letter Alerts:** When all 3 notification attempts fail for a channel, should an internal alert be raised to system administrators?
6. **De-escalation Lifecycle & Target Status:** When a ticket is de-escalated, should it revert back to its exact pre-escalation status (e.g., `open` vs. `in_progress`), or always reset to a fixed status? Does de-escalation require a mandatory reason, and should notifications be dispatched to the same channels and stakeholders to confirm resolution of the escalation?

---

## 2. Assumptions Made

1. **Escalation Precondition:** A ticket can only be escalated if its current status is not already `Escalated` or `Closed`.
2. **Notification Recipients:** Email defaults to the configured escalation supervisor (`NOTIFICATION_ESCALATION_EMAIL`), and Slack sends to the incoming webhook (`SLACK_NOTIFICATION_WEBHOOK_URL`).
3. **Async Processing:** Ticket status updates synchronously in the database for instant user feedback (< 100ms), while notification delivery and retries are deferred to a background queue (`database` queue driver).
4. **Retry Thresholds:** Failed notifications retry up to **3 times** with progressive backoff delays of **10s, 60s, and 180s**.
5. **Unified API Contract:** All API responses adhere to a consistent envelope using [`yousef-ahmed-abdalgawad/laravel-api-responder`](https://packagist.org/packages/yousef-ahmed-abdalgawad/laravel-api-responder) **(built by myself)**.
6. **Audit Trail:** Every delivery attempt, channel target, retry count, and failure error is recorded in a dedicated `notification_logs` table.
7. **Authentication Middleware:** The assessment specification does not mention authentication requirements. Auth middleware (`auth:sanctum`) is intentionally omitted from the escalation endpoint to allow frictionless evaluation without requiring token setup. In a production deployment, the route group would be protected with `->middleware('auth:sanctum')` and authorized via a `TicketPolicy@escalate` gate check.
8. **De-escalation Handling & State Restoration:** A ticket can be de-escalated when its status is `Escalated`. Upon de-escalation (`POST /api/tickets/{id}/de-escalate`), the ticket's state safely reverts back to its recorded `previous_status` (e.g. `in_progress` or `open`), clears `escalated_at`, records an audit trail entry prefixed with `[De-escalated]` in `ticket_escalations`, and dispatches asynchronous multi-channel de-escalation notifications (Email & Slack) via `SendDeescalationNotification` with the exact same 3-retry resilience policy.

---

## 3. Recommendations & Identified Improvements

1. **SLA-Based Automated Escalation:** Implement a scheduled command (`php artisan tickets:check-sla`) to auto-escalate tickets breaching response time limits.
2. **Idempotency Protection:** Enforce an `Idempotency-Key` header on `POST /api/tickets/{id}/escalate` to guard against double-submissions from slow network connections.
3. **Circuit Breaker Pattern:** Temporarily pause dispatching to a failing third-party provider if 10 consecutive deliveries fail, avoiding queue congestion.
4. **Dynamic Tenant Preferences:** Allow SaaS tenant administrators to toggle channels on/off and configure custom webhook URLs directly from an administrative dashboard rather than relying exclusively on `.env`.

---

## 4. Database Design

### Tables Created / Modified

#### 1. Modified: `tickets`
*Tracks ticket entities and their escalation state.*
- `id` (`BIGINT UNSIGNED`, PK, Auto-Increment)
- `subject` (`VARCHAR(255)`, NOT NULL)
- `description` (`TEXT`, NULLABLE)
- `status` (`VARCHAR(50)`, NOT NULL, Default: `'open'`)
- **`previous_status`** (`VARCHAR(20)`, NULLABLE) — *Added to store pre-escalation status for state restoration upon de-escalation*
- `priority` (`VARCHAR(50)`, NOT NULL, Default: `'medium'`)
- `customer_id` (`BIGINT UNSIGNED`, FK ➔ `customers.id`)
- `agent_id` (`BIGINT UNSIGNED`, NULLABLE, FK ➔ `users.id`)
- **`escalated_at`** (`TIMESTAMP`, NULLABLE) — *Added to capture escalation timestamp; reset to null upon de-escalation*
- `created_at`, `updated_at` (`TIMESTAMP`, NULLABLE)

#### 2. New: `ticket_escalations`
*Immutable historical log of escalation and de-escalation events.*
- `id` (`BIGINT UNSIGNED`, PK, Auto-Increment)
- `ticket_id` (`BIGINT UNSIGNED`, FK ➔ `tickets.id`, ON DELETE CASCADE)
- `escalated_by` (`BIGINT UNSIGNED`, NULLABLE, FK ➔ `users.id`, ON DELETE SET NULL)
- `reason` (`VARCHAR(255)`, NULLABLE)
- `created_at`, `updated_at` (`TIMESTAMP`, NULLABLE)

#### 3. New: `notification_logs`
*Detailed audit record for retry tracking and channel delivery status.*
- `id` (`BIGINT UNSIGNED`, PK, Auto-Increment)
- `ticket_id` (`BIGINT UNSIGNED`, FK ➔ `tickets.id`, ON DELETE CASCADE)
- `channel` (`VARCHAR(50)`, NOT NULL) — e.g., `'email'`, `'slack'`
- `recipient` (`VARCHAR(255)`, NOT NULL) — email address, webhook URL, phone number
- `attempt` (`TINYINT UNSIGNED`, NOT NULL, Default: `1`)
- `status` (`VARCHAR(50)`, NOT NULL, Default: `'pending'`) — `pending`, `sent`, `failed`, `exhausted`
- `error_message` (`TEXT`, NULLABLE)
- `sent_at` (`TIMESTAMP`, NULLABLE)
- `created_at`, `updated_at` (`TIMESTAMP`, NULLABLE)

### Relationships
- `customers` **1 ➔ N** `tickets`
- `users` (Agents) **1 ➔ N** `tickets`
- `tickets` **1 ➔ N** `ticket_escalations`
- `tickets` **1 ➔ N** `notification_logs`

### Indexes & Constraints Added
- **Foreign Keys:** Cascade deletes on `ticket_escalations.ticket_id` and `notification_logs.ticket_id`.
- **Composite Index `tickets(status, priority)`:** Optimizes dashboard queries filtering active, escalated tickets.
- **Index `tickets(escalated_at)`:** Speeds up reporting queries and SLA metrics.
- **Composite Index `notification_logs(ticket_id, channel)`:** Accelerates lookups of delivery status per ticket and channel.
- **Composite Index `notification_logs(status, attempt)`:** Optimizes queue diagnostics and dead-letter detection.

---

## 5. Architecture

### Folder Structure (Multi-Layered Architecture)

```
app/
├── Contracts/
│   ├── NotificationChannelInterface.php        # Strategy contract for channel drivers (with context support)
│   └── NotificationChannelManagerInterface.php # Contract for notification dispatcher
├── DTOs/
│   ├── DeescalateTicketDTO.php                 # Data transfer object for ticket de-escalation
│   └── EscalateTicketDTO.php                   # Strongly-typed data transfer object for escalation
├── Enums/
│   ├── NotificationStatus.php                  # Pending, Sent, Failed, Exhausted
│   ├── TicketPriority.php                      # Low, Medium, High, Urgent
│   └── TicketStatus.php                        # Open, InProgress, Escalated, Resolved, Closed
├── Http/
│   ├── Controllers/
│   │   └── Shared/
│   │       ├── TicketController.php            # Presentation: ticket list/show + Inertia.js views
│   │       ├── TicketDeescalationController.php# Presentation: ticket de-escalation API endpoint
│   │       └── TicketEscalationController.php  # Presentation: escalation API endpoint (ApiResponser)
│   ├── Requests/
│   │   └── Shared/
│   │       ├── DeescalateTicketRequest.php     # Form Request: de-escalation validation & DTO factory
│   │       └── EscalateTicketRequest.php       # Form Request: validation & DTO factory
│   └── Resources/
│       └── Shared/
│           ├── EscalationResource.php          # API Resource: transforms escalation payload
│           ├── NotificationLogResource.php     # API Resource: transforms notification log payload
│           └── TicketResource.php              # API Resource: transforms ticket payload
├── Jobs/
│   ├── SendDeescalationNotification.php        # Infrastructure: queued job for de-escalation ($tries = 3, $backoff)
│   └── SendEscalationNotification.php          # Infrastructure: queued job ($tries = 3, $backoff = [10, 60, 180])
├── Mail/
│   ├── TicketDeescalatedMail.php               # Mailable for de-escalation notifications
│   └── TicketEscalatedMail.php                 # Mailable for escalation email notifications
├── Models/
│   ├── Customer.php
│   ├── NotificationLog.php
│   ├── Ticket.php                              # Includes previous_status & isDeescalatable helper
│   ├── TicketEscalation.php
│   └── User.php
├── Providers/
│   └── EscalationServiceProvider.php           # Service Container bindings for interfaces
├── Repositories/
│   └── Shared/
│       ├── NotificationLogRepositoryInterface.php       # Data access contract for audit logs
│       ├── TicketEscalationRepositoryInterface.php      # Data access contract for escalations
│       ├── TicketRepositoryInterface.php                # Data access contract for tickets (with deescalate)
│       ├── EloquentNotificationLogRepository.php        # Implements NotificationLogRepositoryInterface
│       ├── EloquentTicketEscalationRepository.php       # Implements TicketEscalationRepositoryInterface
│       └── EloquentTicketRepository.php                 # Implements TicketRepositoryInterface
└── Services/
    └── Shared/
        ├── Notification/
        │   ├── EmailChannel.php                # Implements NotificationChannelInterface (Escalate + Deescalate)
        │   ├── SlackChannel.php                # Implements NotificationChannelInterface (Escalate + Deescalate)
        │   ├── NotificationChannelManager.php  # Implements NotificationChannelManagerInterface
        │   └── NotificationResult.php          # Value object for channel send results
        ├── TicketEscalationService.php         # Implements TicketEscalationServiceInterface (escalate + deescalate)
        └── TicketEscalationServiceInterface.php # Business orchestration contract
```

### Design Decisions

1. **Multi-Layered Architecture:**
   - **Presentation Layer (Controllers, Form Requests, API Resources):**
     - `EscalateTicketRequest` validates HTTP input and constructs a typed `EscalateTicketDTO`.
     - `TicketEscalationController` orchestrates the HTTP response without containing business or SQL logic.
     - `TicketResource` formats and filters model attributes into a clean API response schema.
   - **Data Transfer Objects (DTO):**
     - `EscalateTicketDTO` carries typed, validated parameters (`ticketId`, `escalatedBy`, `reason`) across layer boundaries, preventing HTTP request leaks into domain services.
   - **Application / Service Layer:**
     - `TicketEscalationService` encapsulates business rules (eligibility check, state transitions, dispatching jobs) while depending solely on repository and notification interfaces.
   - **Data Access Layer (Repository Pattern):**
     - `TicketRepositoryInterface`, `TicketEscalationRepositoryInterface`, and `NotificationLogRepositoryInterface` decouple business logic from Eloquent queries, making database engines and query strategies interchangeable and easily mockable in tests.
   - **Infrastructure Layer:**
     - Handles external interactions (database queue worker, SMTP mail service, Slack incoming webhooks).
2. **API Response Standardization:** Uses [`yousef-ahmed-abdalgawad/laravel-api-responder`](https://packagist.org/packages/yousef-ahmed-abdalgawad/laravel-api-responder) **(My open-source package — built and authored by myself)** via the `ApiResponser` trait. All endpoints return a uniform envelope (`status`, `success`, `message`, `data`). Unhandled exceptions are automatically formatted via `ApiExceptionHandler::register($exceptions)` in `bootstrap/app.php`.
3. **Authentication & Authorization Design (Auth-Aware Architecture):**
   - **Assessment Evaluation vs. Production:** To allow frictionless evaluation of the take-home assessment (without requiring reviewers to register, log in, or configure Bearer tokens in headers or frontend requests), the endpoints are open by default.
   - **Auth-Aware Attribution:** The controller checks `$request->user()`. If an authenticated user is present (via Laravel Sanctum token or session), they are automatically attributed as the `escalated_by` user. If unauthenticated, `escalated_by` can be optionally supplied in the payload.
   - **Production Hardening:** In a live SaaS deployment, the route group is protected with `->middleware('auth:sanctum')` and authorized via a Laravel Policy (`TicketPolicy@escalate`).
4. **Interactive API Documentation (Scribe):**
   - Implemented using [`knuckleswtf/scribe`](https://github.com/knuckleswtf/scribe).
   - Generates interactive, try-it-out HTML documentation at `/docs`, an OpenAPI 3.0 specification at `/docs.openapi`, and downloadable Postman collection at `/docs.postman`.
   - Annotated controllers and form requests with detailed parameter descriptions, sample responses (200, 404, 422), and scenario definitions.
5. **Loose Coupling & Full Dependency Injection:** Interfaces are injected via PHP 8 constructor property promotion across all layers. Zero concrete instantiation (`new`) in services or controllers.
6. **Strategy Pattern:** Each notification delivery channel implements `NotificationChannelInterface` and is dynamically resolved via `NotificationChannelManager` (**Open/Closed Principle**).

### Notification Architecture
- Triggered by `TicketEscalationService::escalate(Ticket $ticket)`.
- Dispatches asynchronous `SendEscalationNotification` jobs to the queue.
- `NotificationChannelManager` iterates through enabled channels and invokes `send(Ticket $ticket)`.

### Retry Strategy
- Handled natively in `SendEscalationNotification`:
  - `public int $tries = 3;`
  - `public array $backoff = [10, 60, 180];` (10s, 1m, 3m delays)
- Every attempt increments `notification_logs.attempt`.
- Transient errors log as `'failed'`.
- If all 3 attempts fail, Laravel calls `failed(Throwable $exception)`, transitioning status to `'exhausted'`.

### Adding Future Notification Channels (e.g., WhatsApp, Teams, SMS)
Adding a new channel requires zero changes to existing controllers or services:
1. **Create the Driver Class:** Implement `NotificationChannelInterface`:
   ```php
   class WhatsAppChannel implements NotificationChannelInterface {
       public function name(): string { return 'whatsapp'; }
       public function send(Ticket $ticket, array $payload = []): NotificationResult { ... }
   }
   ```
2. **Register in Configuration:** Add the class to `config/escalation.php` under `'channels'`.
3. **Set Environment Credentials:** Add required API tokens/webhooks to `.env`.

---

## 6. Testing

### Test Cases

1. **Successful Escalation (`POST /api/tickets/{id}/escalate`):**
   - Asserts ticket status updates to `Escalated`.
   - Asserts `escalated_at` timestamp is set.
   - Asserts `SendEscalationNotification` job is pushed to the queue.
   - Asserts response JSON: `{"status": 200, "success": true, ...}`.
2. **Invalid Ticket (404 Not Found):**
   - Requests non-existent ticket ID (`/api/tickets/99999/escalate`).
   - Asserts `ApiExceptionHandler` intercepts `ModelNotFoundException` and returns unified error structure: `{"status": 404, "success": false, ...}`.
3. **Already Escalated Ticket (422 Unprocessable):**
   - Attempts escalation on a ticket already in `Escalated` status.
   - Asserts response status is `422` with message `"Ticket is already escalated."` and no duplicate jobs are queued.
4. **Notification Failure (Attempt 1):**
   - Simulates external webhook returning HTTP 500.
   - Asserts `notification_logs` records `status = 'failed'` and `attempt = 1`.
   - Asserts job is released back to queue for retry with backoff.
5. **Retry Success (Attempt 2):**
   - Simulates temporary failure on attempt 1, followed by success on attempt 2.
   - Asserts `notification_logs` updates to `attempt = 2`, `status = 'sent'`, and populates `sent_at`.
6. **Retry Exhausted (Attempt 3 Fails):**
   - Simulates persistent failure across all 3 attempts.
   - Asserts `notification_logs` transitions to `status = 'exhausted'` with the failure reason recorded.
7. **Loose Coupling Mock Verification:**
   - Swaps `NotificationChannelManagerInterface` in the service container with a mock to verify isolation without making real network or queue calls.

---

## 7. Self-Testing Notes

- **Test Suite Results:** Executed `php artisan test` with **26 passed tests and 117 assertions** with zero failures or errors across both Feature and Unit test suites:
  - `Tests\Feature\TicketEscalationApiTest` (12 tests, 58 assertions): Verified successful ticket escalation, timestamp update, escalation log creation, queued job dispatches, auth-aware automatic attribution when logged in via Sanctum, 404 response on invalid ticket, 422 on unescalatable tickets (data provider across `Escalated`, `Resolved`, `Closed`), valid status escalations (data provider for `Open`, `InProgress`), validation failure on invalid `escalated_by`, API index pagination, and single ticket retrieval.
  - `Tests\Feature\NotificationRetryTest` (6 tests, 17 assertions): Verified successful notification logging (`sent` status and `sent_at`), failed attempt logging (`failed` status and error message with queue retry throw), retry exhaustion logging (`exhausted` status at max tries), EmailChannel delivery via Mailable `TicketEscalatedMail` and `Mail::fake()`, SlackChannel webhook dispatch via `Http::fake()`, and SlackChannel failure/timeout handling.
  - `Tests\Unit\NotificationChannelManagerTest` (3 tests, 6 assertions): Verified registration and resolution of channels, null handling on unknown channels, and pluggable extension with future custom channels (e.g., WhatsApp).
  - `Tests\Unit\TicketEscalationServiceTest` (3 tests, 34 assertions): Verified service decoupling via mock interfaces, transaction handling, domain exception guarding on invalid statuses, and synchronous notification dispatch logging.
  - `Tests\Feature\ExampleTest` & `Tests\Unit\ExampleTest`: Baseline sanity tests passing.
- **Interactive Documentation (Scribe):** Generated interactive API docs at `http://localhost:8000/docs`, OpenAPI 3.0 specs at `/docs.openapi`, and auto-updating Postman collection at `/docs.postman`.
- **Postman Collection Deliverable:** Created `HelpDesk_Escalations.postman_collection.json` in project root covering:
  - `1. Interactive HTML Docs (Scribe)` (`GET /docs`)
  - `2. OpenAPI 3.0 Specification` (`GET /docs.openapi`)
  - `3. List Tickets (Paginated)` (`GET /api/tickets`)
  - `4. Get Single Ticket Details` (`GET /api/tickets/{id}`)
  - `5. Escalate Ticket (Frictionless / Body Attribution)` (`POST /api/tickets/{id}/escalate`)
  - `6. Escalate Ticket (Sanctum Authenticated)` (`POST /api/tickets/{id}/escalate` with Bearer token)
  - `7. Escalate Ticket - Validation / Domain Failure (422)`
  - `8. Escalate Non-Existent Ticket (404)`
  - `9. Tickets Web Dashboard` (`GET /tickets`)
  - `10. Ticket Detail & Escalation Page` (`GET /tickets/{id}`)
- **Frontend Verification:** Built production bundle with `npm run build` compiling client assets (`Home.jsx`, `Tickets/Index.jsx`, `Tickets/Show.jsx`) with Vite and Tailwind CSS v4 in 2.37s with zero errors.
- **Database & Seeder Verification:** Ran `php artisan migrate:fresh --seed` successfully populating customers, agents, and tickets with varied statuses and priorities.

