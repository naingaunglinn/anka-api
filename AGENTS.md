# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Commands

```bash
# Development (runs API server, queue, log streaming, and Vite concurrently)
composer dev

# First-time setup
composer setup

# Run all tests
composer test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run tests matching a filter
php artisan test --filter=SomeTestName

# Lint / fix code style
php ./vendor/bin/pint

# Frontend build
npm run build
npm run dev
```

## Architecture

**ANKA** is a multi-tenant SaaS API backend (Laravel 13, PHP 8.3+) serving a Next.js frontend. PostgreSQL (Supabase) is used in production; SQLite is used for tests.

### Multi-Tenancy

Every request must include an `X-Tenant-ID` header. The `TenantScope` middleware validates this and binds the tenant to the container. All primary models use the `BelongsToTenant` trait, which:
- Adds a global Eloquent scope that filters all queries to the current tenant's `tenant_id`
- Auto-injects `tenant_id` on model creation

This prevents cross-tenant data leakage. Never bypass this scope unless explicitly needed.

### Request / Response

- All API routes live in `routes/api.php` under `/api` prefix, protected by `auth:sanctum` except for `/login`
- Controllers are in `app/Http/Controllers/Api/`
- JSON responses are shaped by Laravel API Resources in `app/Http/Resources/`
- CORS is restricted to `localhost:3000` and `localhost:3001`

### Key Business Logic

**Deal → Contract → Project flow:** Calling `POST /deals/{deal}/win` invokes the PostgreSQL stored procedure `win_deal()`, which atomically converts a Deal into a Contract and Project. Do not replicate this logic in PHP — it lives in the database.

**Invoice payments (`PATCH /invoices/{invoice}/pay`):** Runs inside `DB::transaction()` and simultaneously increments `contracts.revenue_recognized`.

**TimeEntry approval (`PATCH /time-entries/{id}/approve`):** Uses `lockForUpdate()` (pessimistic locking) inside a transaction before incrementing `projects.consumed_hours`.

### Models

| Model | Notable relationships / fields |
|-------|-------------------------------|
| `Deal` | `ghost_roles`, `hard_assignments`, `estimation_resources`, `deal_overheads` relationships |
| `Contract` | Tracks `total_value` vs `revenue_recognized` |
| `Invoice` | `total` is a PostgreSQL generated column — do not set it in PHP |
| `Project` | Tracks `consumed_hours` |
| `TimeEntry` | Has `status` (default `Draft`) and approval workflow |

All models use UUID primary keys.

### Authentication

Laravel Sanctum (stateless token-based). Passwords hashed at bcrypt cost 12.

### Testing

PHPUnit 12. Test environment uses in-memory SQLite, array cache, and sync queue (configured in `phpunit.xml`). Tests live in `tests/Feature/` and `tests/Unit/`.
