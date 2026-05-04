# Security

## Reporting a vulnerability

Open a private GitHub security advisory or email the maintainers directly. Do not file public issues for security reports.

---

## Architecture overview

Anka API is a Laravel 13 multi-tenant SaaS backend. It is consumed exclusively by the Anka Frontend (Next.js 16). All routes require Sanctum token authentication except `POST /login`.

---

## Multi-tenancy / tenant isolation strategy

### How isolation works

Every HTTP request must carry an `X-Tenant-ID` header. The `tenant` middleware (`App\Http\Middleware\TenantScope`) reads this header, validates the tenant exists, and binds the `tenant_id` into the service container (`app()->instance('tenant_id', $id)`).

All primary Eloquent models use the `BelongsToTenant` trait (`app/Traits/BelongsToTenant.php`), which:

1. **Adds a global Eloquent scope** — every query on the model automatically appends `WHERE tenant_id = <current_tenant>`. Cross-tenant reads are impossible unless the scope is explicitly removed.
2. **Auto-injects `tenant_id` on create** — the `creating` model event sets `tenant_id` from the container, preventing models from being created under the wrong tenant even if the caller forgets to set it.

### Tenant isolation audit — model coverage

| Model | BelongsToTenant | Notes |
|---|---|---|
| `User` | ✅ | Auth principal scoped to tenant |
| `Deal` | ✅ | SoftDeletes also respects scope |
| `Contract` | ✅ | Created only via `win_deal()` stored procedure |
| `Invoice` | ✅ | `total` is a generated column — never set in PHP |
| `Project` | ✅ | Created only via `win_deal()` stored procedure |
| `TimeEntry` | ✅ | Approval flow uses `lockForUpdate()` inside transaction |

All models that touch tenant data carry the trait. No model reads cross-tenant data.

### Defense-in-depth gaps to monitor

- **X-Tenant-ID is not authenticated** — any authenticated user can pass any tenant ID. Future hardening: verify the authenticated user's `tenant_id` matches the header value in the `tenant` middleware, and return 403 on mismatch.
- **Stored procedure `win_deal()`** — executed via `DB::select()` directly. Confirm the PostgreSQL role used by Laravel has no cross-schema privileges.

---

## Authentication

| Control | Implementation |
|---|---|
| Mechanism | Laravel Sanctum (stateless Bearer tokens) |
| Password hashing | bcrypt, cost 12 (`BCRYPT_ROUNDS=12`) |
| Token storage | Frontend stores token in-memory (Zustand) + httpOnly cookie — never `localStorage` |
| CSRF | Sanctum CSRF cookie auto-fetched before every mutating request |
| Logout | `DELETE /api/logout` revokes the current token via `$request->user()->currentAccessToken()->delete()` |

---

## Rate limiting

Throttle middleware is applied in `routes/api.php`:

| Route | Limit | Reason |
|---|---|---|
| `POST /login` | `throttle:5,1` (5 req/min/IP) | Brute-force protection |
| All auth routes | `throttle:60,1` (60 req/min/user) | General API rate limiting |

Laravel's built-in throttle middleware automatically returns a `Retry-After` header on HTTP 429 responses. The frontend reads this header and displays a countdown toast (see `lib/axios.ts` and `lib/api.ts` in anka-frontend).

---

## CORS

Configured in `config/cors.php`:

| Setting | Value |
|---|---|
| `allowed_origins` | Read from `FRONTEND_URL` env var; falls back to `localhost:3000/3001` in local dev |
| `allowed_methods` | `GET, POST, PATCH, DELETE, OPTIONS` — PUT is not used by any route |
| `allowed_headers` | `X-Tenant-ID, Authorization, Content-Type, Accept` |
| `exposed_headers` | `Retry-After` — required for the frontend countdown toast on 429 |
| `max_age` | 7200 s — reduces preflight OPTIONS requests in production |
| `supports_credentials` | `true` — required for Sanctum session cookie |

**Production requirement:** Set `FRONTEND_URL=https://your-frontend-domain.com` in the production environment. Never leave it unset — the fallback to localhost is intentional for local dev only.

---

## Input validation

All request input is validated via Laravel Form Request classes before reaching controller logic. The frontend additionally validates all form data with Zod schemas (`lib/schemas/` in anka-frontend) before the HTTP request is sent.

---

## Sensitive data handling

- `Authorization` header values are never logged in the frontend (see comment in `lib/axios.ts`).
- `X-Tenant-ID` values are not logged.
- No secrets are stored in `localStorage` or `sessionStorage` on the frontend.
- The `__session` cookie is httpOnly — inaccessible to JavaScript.
- Laravel logs (`storage/logs/`) must not be shipped to external log aggregators without scrubbing `Authorization` and `X-Tenant-ID` header values from request context.

---

## Composer dependency audit

As of 2026-05-04 (`composer audit`):

| Package | Severity | Status |
|---|---|---|
| — | — | No vulnerabilities found |

Run `composer audit` before every release.

---

## Checklist for future developers

Before shipping a new feature that touches tenant data:

- [ ] Does every new Eloquent model that stores tenant data use the `BelongsToTenant` trait?
- [ ] Are there any raw `DB::` queries that bypass the global scope? If so, do they manually filter by `tenant_id`?
- [ ] Does any new route expose data without `auth:sanctum` + `tenant` middleware?
- [ ] Does any new route use a verb not in the CORS `allowed_methods` list? Update `config/cors.php` if so.
- [ ] Has `composer audit` been run and all high/critical findings resolved or documented below?
