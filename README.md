# HelpDesk SaaS - Ticket Escalation & Notification System

📑 [**Complete Technical Assessment Document**](docs/TECHNICAL_ASSESSMENT.md) *(Architecture, Database Design, Requirements, Testing, Self-Testing)*

---

## 📋 Overview

This project extends an existing Help Desk SaaS platform (Users, Customers, Agents, Tickets) with a robust, resilient **Ticket Escalation Notification System**.

When a ticket is escalated:
1. Ticket status transitions to **"Escalated"**.
2. Escalation timestamp is captured (`escalated_at`).
3. Escalation notifications are dispatched across configured channels (currently **Email** and **Slack Webhook**).
4. The system is designed with an extensible channel architecture allowing effortless addition of future channels (**WhatsApp**, **SMS**, **Microsoft Teams**, **Push Notifications**).
5. Automatic retry mechanism (up to 3 attempts) tracks delivery attempts, records failures, and preserves final delivery statuses.
6. A reactive web interface (built with **React** and **Inertia.js**) provides ticket oversight and one-click escalation.

---

## 🛠 Tech Stack

- **Backend Framework:** Laravel 13.x (PHP 8.3+)
- **API Standards & Response Normalization:** 🌟 [`yousef-ahmed-abdalgawad/laravel-api-responder`](https://packagist.org/packages/yousef-ahmed-abdalgawad/laravel-api-responder) **(Built by myself)** — Unified JSON response contracts, consistent error formatting, and observability.
- **Authentication & API:** Laravel Sanctum
- **Frontend Layer:** Inertia.js React Adapter (`@inertiajs/react`), React 19
- **Styling:** Tailwind CSS v4
- **Database:** MySQL
- **Queue / Async Processing:** Laravel Database Queue Worker
- **Build Tool:** Vite 8

---

## 🏗 Architecture & Design Decisions

### 1. Extensible Notification Architecture (Strategy Pattern)

The notification system follows the **Open/Closed Principle (SOLID)** and **Strategy Pattern**:

```
                 ┌────────────────────────────┐
                 │ TicketEscalationService    │
                 └─────────────┬──────────────┘
                               │ Dispatches Async Job
                               ▼
                 ┌────────────────────────────┐
                 │ SendEscalationNotification │
                 └─────────────┬──────────────┘
                               │ Iterates Configured Channels
                               ▼
                ┌───────────────────────────────┐
                │  NotificationChannelManager   │
                └──────────────┬────────────────┘
                               │ Resolves Drivers
        ┌──────────────────────┼──────────────────────┐
        ▼                      ▼                      ▼
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│ EmailChannel │       │ SlackChannel │       │ Future: SMS/ │
│              │       │              │       │ WhatsApp/... │
└──────────────┘       └──────────────┘       └──────────────┘
```

- **`NotificationChannelInterface`**: Enforces a standard contract (`send(Ticket $ticket, array $payload): NotificationResult`).
- **`NotificationChannelManager`**: Resolves registered channel drivers dynamically via Laravel's service container.
- **Adding New Channels in Future**:
  1. Create a new class implementing `NotificationChannelInterface` (e.g., `WhatsAppChannel`).
  2. Register the channel driver in `config/escalation.php` (or via service provider).
  3. No alteration to `TicketEscalationService` or controllers is needed.

---

### 2. Failure Handling & Retry Strategy

Each notification dispatch is handled asynchronously using Laravel Queued Jobs:
- **Tries:** Maximum of 3 attempts (`$tries = 3`).
- **Backoff:** Progressive backoff (e.g., `[10, 60, 180]` seconds) to mitigate transient downstream network or rate-limit issues.
- **Delivery Audit Log:** A dedicated `ticket_escalation_logs` or `notification_logs` table persists:
  - Channel name (e.g., `email`, `slack`)
  - Recipient identifier / endpoint
  - Current attempt count (`attempt`)
  - Delivery status (`pending`, `delivered`, `failed`, `exhausted`)
  - Error logs / exception traces for diagnostics

---

### 3. Standardized API Responses & Loose Coupling

- **Standardized Response Envelope:** Powered by [`yousef-ahmed-abdalgawad/laravel-api-responder`](https://packagist.org/packages/yousef-ahmed-abdalgawad/laravel-api-responder) **(built by myself)**, all API endpoints return a predictable, unified payload (`status`, `success`, `message`, `data`, `pagination`, `meta`).
- **Automated Exception Handling:** `ApiExceptionHandler::register($exceptions)` in `bootstrap/app.php` converts validation failures (422), rate limits (429), model missing (404), and unhandled server errors (500) into standardized JSON without boilerplate try/catch blocks.
- **Loose Coupling & Mockability:** Services and notification channels depend exclusively on abstractions (`NotificationChannelInterface`, etc.) injected via constructor property promotion. Real drivers can be seamlessly swapped with test doubles or mocks in PHPUnit tests without tight coupling.


---

### 4. Database Design

| Table | Purpose / Key Columns |
|---|---|
| `tickets` | Core entity: `id`, `subject`, `priority`, `status` (`open`, `escalated`, `resolved`, etc.), `escalated_at`, `customer_id`, `agent_id` |
| `ticket_escalations` | Records each escalation event: `id`, `ticket_id`, `escalated_by`, `reason`, `created_at` |
| `notification_logs` | Audit trail for delivery: `id`, `ticket_id`, `channel`, `recipient`, `attempt`, `status`, `error_message`, `sent_at` |

---

## 🚀 Setup Instructions

### Prerequisites
- **PHP:** >= 8.3 (with `pdo_mysql`, `mbstring`, `curl`, `openssl` extensions enabled)
- **Composer:** >= 2.x
- **Node.js:** >= 20.x & **NPM**
- **MySQL:** Running on port `3306` (e.g., via Laragon, Docker, or MySQL service)

---

### Step 1: Clone & Configure Environment

```bash
# Clone the repository (if not already local)
git clone <repo-url> helpdeskflorgics
cd helpdeskflorgics

# Copy environment file
cp .env.example .env
```

Ensure your `.env` contains the correct database and queue configuration:

```ini
APP_NAME="HelpDesk"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=florgics
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

# Optional Notification Webhooks
SLACK_NOTIFICATION_WEBHOOK_URL=
NOTIFICATION_ESCALATION_EMAIL=supervisor@example.com
```

---

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Generate application encryption key
php artisan key:generate

# Install Node modules
npm install
```

---

### Step 3: Run Database Migrations & Seeds

```bash
# Run database migrations
php artisan migrate

# (Optional) Seed demo tickets and users
php artisan db:seed
```

---

### Step 4: Run Development Services

Start the development services in separate terminal windows:

#### 1. Start Laravel Backend Server:
```bash
php artisan serve
```
App will be accessible at: `http://127.0.0.1:8000`

#### 2. Start Vite Dev Server (React + Inertia HMR):
```bash
npm run dev
```

#### 3. Start Queue Worker (for Retries and Notification Dispatch):
```bash
php artisan queue:work --tries=3 --backoff=10,60,180
```

---

## 📡 API Endpoints

### Escalate Ticket
- **Method:** `POST`
- **URL:** `/api/tickets/{id}/escalate`
- **Headers:** `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": 200,
  "success": true,
  "message": "Ticket escalated successfully. Notifications dispatched.",
  "data": {
    "id": 1,
    "subject": "System crash on payment processing",
    "status": "Escalated",
    "priority": "High",
    "escalated_at": "2026-09-14T15:10:00Z"
  }
}
```

## 📖 Interactive API Documentation (Scribe)

Comprehensive interactive API documentation is generated via [Knuckles Scribe](https://github.com/knuckleswtf/scribe):

- **Interactive HTML Documentation:** [http://localhost:8000/docs](http://localhost:8000/docs) (includes Try-It-Out console and code examples in cURL and JavaScript)
- **OpenAPI 3.0 Specification:** [http://localhost:8000/docs.openapi](http://localhost:8000/docs.openapi)
- **Scribe Postman Collection Export:** [http://localhost:8000/docs.postman](http://localhost:8000/docs.postman)

To regenerate docs after code or docblock changes:
```bash
php artisan scribe:generate
```

---

## 🔒 Authentication & Authorization (Auth-Aware)

- **Frictionless Review Evaluation:** The assessment endpoints are open by default so evaluators can immediately test with Postman, cURL, or the React frontend without login obstacles.
- **Auth-Aware Attribution:** The controller inspects `$request->user()`. If a Laravel Sanctum Bearer token is provided (`Authorization: Bearer <token>`), the authenticated user is automatically bound as the `escalated_by` agent. If unauthenticated, `escalated_by` can be optionally supplied in the JSON payload.
- **Production Hardening:** To restrict the route strictly to authenticated agents in production, attach `->middleware('auth:sanctum')` and authorize via `Gate::authorize('escalate', $ticket)`.

---

## 🧪 Testing

Run PHPUnit tests verifying happy paths, validation, failures, auth attribution, and retry logic (**26 tests, 117 assertions**):

```bash
# Run full feature and unit test suite
php artisan test

# Filter specifically for ticket escalation API tests
php artisan test --filter=TicketEscalationApiTest

# Filter specifically for notification and retry tests
php artisan test --filter=NotificationRetryTest
```

---

## 📮 Postman Collection

A complete Postman collection is included in the project root:
- [`HelpDesk_Escalations.postman_collection.json`](HelpDesk_Escalations.postman_collection.json)

It includes preconfigured requests for:
1. **Interactive HTML Docs (Scribe)** (`GET /docs`)
2. **OpenAPI 3.0 Specification** (`GET /docs.openapi`)
3. **List Tickets (Paginated)** (`GET /api/tickets`)
4. **Get Single Ticket Details** (`GET /api/tickets/:id`)
5. **Escalate Ticket (Frictionless / Body Attribution)** (`POST /api/tickets/:id/escalate`)
6. **Escalate Ticket (Sanctum Authenticated)** (`POST /api/tickets/:id/escalate` with Bearer token)
7. **Escalate Ticket - Validation / Domain Failure** (`POST /api/tickets/:id/escalate` with 422 Unprocessable)
8. **Escalate Non-Existent Ticket** (`POST /api/tickets/999999/escalate` with 404 Not Found)
9. **Frontend Tickets Web Dashboard** (`GET /tickets`)
10. **Frontend Ticket Detail & Escalation Page** (`GET /tickets/:id`)

---

## 📚 Deliverables & Documentation Link

All requirements, architectural designs, database schemas, test cases, and self-testing logs are consolidated in:
- [**Complete Technical Assessment Document**](docs/TECHNICAL_ASSESSMENT.md)
  - **Section 1:** Questions for the Product Owner
  - **Section 2:** Assumptions Made
  - **Section 3:** Recommendations & Improvements
  - **Section 4:** Database Design & Schema Notes (Tables, Relationships, Indexes)
  - **Section 5:** Multi-Layered Architecture & Notification Strategy Pattern (with [`yousef-ahmed-abdalgawad/laravel-api-responder`](https://packagist.org/packages/yousef-ahmed-abdalgawad/laravel-api-responder) — **built by myself**)
  - **Section 6:** Test Cases & Verification Scenarios
  - **Section 7:** Self-Testing Notes & Audit Log
