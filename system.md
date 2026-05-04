# ANKA System Architecture & Analysis

## 1. System Overview
**ANKA** is a multi-tenant SaaS agency management platform designed to handle CRM (Deals), Contracts, Billing (Invoices), and Project Management (Time Tracking). 
The backend is a robust RESTful API built on **Laravel 12**, serving a frontend built with **Next.js 16 (App Router)** and **Zustand** for state management. 

## 2. Architecture Stack
- **Backend Framework:** Laravel 12
- **Database:** PostgreSQL 15+ (hosted via Supabase)
- **Authentication:** Laravel Sanctum (Token-based)
- **Multi-tenancy:** Header-based (`X-Tenant-ID`), enforced via Middleware and Eloquent Global Scopes.

## 3. Multi-Tenancy Strategy
The system follows a strict Row-Level Multi-Tenancy model ensuring absolute data isolation across different agencies on the SaaS.
- **Middleware (`TenantScope`):** Intercepts every incoming request to protected routes. It requires the `X-Tenant-ID` header containing the tenant's UUID. If present, it binds `tenant_id` to the application container.
- **Eloquent Global Scope (`BelongsToTenant`):** A custom trait applied to all primary models (`User`, `Deal`, `Contract`, `Invoice`, `Project`, `TimeEntry`). It automatically appends a `WHERE tenant_id = ?` clause to all read queries and injects the `tenant_id` during model creation, ensuring data isolation without repetitive controller logic.

## 4. Database Schema & Data Integrity
The database leverages powerful PostgreSQL native features to offload computational overhead from the application layer and maintain strict integrity:
- **Primary Keys:** Exclusively UUIDs (`gen_random_uuid()`), cast as strings in Laravel.
- **Readable Identifiers:** Contracts, Invoices, and Projects use PostgreSQL sequences for human-readable IDs (e.g., `CON-0001`, `INV-1042`).
- **PostgreSQL Generated Columns:**
  - `employees.cost_per_hour`: Auto-calculated via `monthly_salary / workable_hours`.
  - `invoices.total`: Auto-calculated via `amount + tax`.
  *(Laravel's Eloquent ignores updating these columns directly, avoiding write conflicts).*
- **Stored Procedures:** The system uses a native PostgreSQL stored procedure `win_deal(p_deal_id, p_tenant_id)` to atomically convert a won Deal into a Contract and Project without risking partial or broken states in PHP memory.

## 5. API Endpoints & Services

### Authentication (`/api/auth` & `/api/login`)
- `POST /login`: Validates credentials against the database and issues a Sanctum Bearer token.
- `POST /logout`: Revokes the current token.
- `GET /auth/me`: Returns the authenticated user, role (`app_role`), and bound `tenant_id`.

### CRM / Deals (`/api/deals`)
- Handles the sales pipeline. 
- `GET /deals`: Returns deals eagerly loaded with nested relationships (`ghost_roles`, `hard_assignments`, `estimation_resources`, `deal_overheads`).
- `PATCH /deals/{id}/stage`: Optimized endpoint for Kanban board drag-and-drop, purely updating stage and win probability.
- `POST /deals/{id}/win`: Triggers the PostgreSQL stored procedure `win_deal`. Returns the refreshed deal alongside the newly generated Contract and Project records.

### Contracts & Billing (`/api/contracts`, `/api/invoices`)
- Tracks financial agreements and revenue.
- `PATCH /invoices/{id}/pay`: Processes invoice payment using database transactions. It marks the invoice as paid and atomically increments `contracts.revenue_recognized` simultaneously.

### Projects & Time Tracking (`/api/projects`, `/api/time-entries`)
- `POST /time-entries`: Creates new time entries in a default `Draft` status.
- `PATCH /time-entries/{id}/approve`: Critical operation using pessimistic database locking (`lockForUpdate()`). Ensures the entry is marked approved exactly once, and atomically increments `projects.consumed_hours` to ensure the project budget perfectly matches timesheets, completely preventing race conditions under heavy concurrent use.

## 6. Security Considerations
- **CORS Configuration:** Strictly scoped to `http://localhost:3000` and `http://localhost:3001` for the frontend. Allowed headers explicitly include `X-Tenant-ID` and `Authorization`.
- **Atomic Operations:** Critical financial (Invoice payments) and time-tracking (approvals) operations are wrapped in `DB::transaction()` with Row-Level locks.
- **Data Leaks:** The `BelongsToTenant` global scope guarantees that cross-tenant data spillage is impossible via Eloquent queries, protecting against IDOR (Insecure Direct Object Reference) attacks natively.
- **Stateless APIs:** Adhering strictly to stateless tokens via Laravel Sanctum ensures horizontal scalability in the backend.

## 7. Response Formatting
All API responses use Laravel API Resources. This ensures that the JSON structure is standardized (wrapped in a `data` key) and all keys are consistently returned in `snake_case`. The Next.js frontend is responsible for interpreting and mapping these to `camelCase` for strict TypeScript typings.
