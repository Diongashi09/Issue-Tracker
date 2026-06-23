# Issue Tracker

A full-stack Laravel 13 issue tracker with project management, AJAX-driven interactions, role-based authorization, and a full PHPUnit test suite.

---

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- SQLite (built into PHP — used for tests) or MySQL/MariaDB for production

---

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Copy the environment file and generate an application key
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env, then migrate and seed
php artisan migrate:fresh --seed

# 5. Build front-end assets
npm run build

# 6. Start the development server
php artisan serve
```

Visit http://localhost:8000 and log in with the seeded credentials below.

---

## Seeded Login Credentials

| Email | Password | Role |
|---|---|---|
| `test@example.com` | `password` | Demo user — owns projects and issues |

The seeder also creates four additional project owners and two users with **no owned projects** — useful for verifying the authorization boundary (they can browse but cannot edit others' work).

To reset to a clean slate at any time:

```bash
php artisan migrate:fresh --seed
```

---

## Running Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`) so they run without affecting your development database. The full suite covers:

- Project and Issue CRUD (happy path + guest redirects)
- Policy enforcement (owner-only 403s, non-owner tag/member operations)
- AJAX endpoints (`postJson`/`getJson` — JSON shape, status codes, DB side-effects)
- Validation (missing fields, duplicate tag names, invalid enum values, deadline rules)
- Search and filter composition

---

## Architecture Decisions

### Bootstrap 5 instead of Tailwind

Laravel Breeze scaffolds with Tailwind. The assignment specifies Bootstrap 5, so Tailwind was removed in a dedicated commit (replacing the Vite config, layout files, and all auth views) to keep the history clean. Mixing both would have produced visual chaos and contradicted the brief.

### JSON-partial AJAX — server renders, JavaScript swaps

Every AJAX endpoint returns `{ html: "..." }` where the value is a server-rendered Blade partial — the **same file** used on first paint. Comments, tags, and members all share this pattern. The alternative (building DOM strings in JavaScript) duplicates markup, risks XSS if escaping slips, and diverges from the server's rendering as the app evolves. With Blade as the single rendering authority, the partial is consistent whether it arrives via a full-page load or an XHR.

### Content-negotiated `issues.index`

`IssueController@index` returns a full Blade view for browser requests and `{ html, pagination }` JSON for AJAX requests (`expectsJson()` — set automatically by the `Accept: application/json` header in `lib/http.js`). One endpoint, one query, two consumers. No separate `/search` route that would have duplicated the filter composition logic.

### Backed enums for status and priority

`IssueStatus` and `IssuePriority` are PHP backed string enums with `label()` and `color()` helpers. They are the single source of truth used for: Eloquent casts, `Rule::enum()` validation, `<select>` option rendering, and badge display. No duplicated string lists anywhere.

### `user_id` excluded from Project's `$fillable`

Ownership is set server-side via `auth()->id()`, not from the request body. A forged `user_id` field in the form payload is silently ignored. The `ProjectTest` includes a specific test that verifies this boundary.

### FK cascade on project → issue deletion

Deleting a project cascades to its issues (and transitively to their comments, tags, and assignments). This is the natural semantic for a tracker — an issue without a project is orphaned and useless. The alternative (restrict) would require manually deleting issues before projects, adding friction with no safety benefit at this scale.

### `syncWithoutDetaching()` for idempotent tag/member attachment

Using `attach()` on a composite primary-key pivot table throws a constraint violation on a duplicate submit. `syncWithoutDetaching()` is a no-op if the relationship already exists, making the `POST issues/{issue}/tags` endpoint safe against double-clicks and network retries.

### `AbortController` on debounced search

The search input fires a fetch request 300 ms after the last keystroke. If another keystroke arrives before the response returns, the previous request is aborted with `AbortController.abort()`. Without this, fast typing produces out-of-order responses that can overwrite newer results with older ones. `AbortError` is caught and silently ignored — it is not a failure, just a cancelled in-flight request.

### `Model::preventLazyLoading()` in non-production

Enabled in `AppServiceProvider::boot()`. Any relationship accessed without being eager-loaded throws an exception in development and test environments. This forces every list page to declare its `with()`/`withCount()` calls explicitly and makes N+1 bugs loud rather than silent.

### `IssuePolicy` checks parent project ownership

Issues do not have a direct owner field. Authority to edit or delete an issue derives from owning the parent project (`$user->id === $issue->project->user_id`). The policy uses `loadMissing('project')` so it is safe whether or not the controller has already eager-loaded the relationship, without double-querying.

### `authorizeResource()` + explicit `authorize()` on nested controllers

`IssueController` uses `$this->authorizeResource(Issue::class, 'issue')` in its constructor, which gates all seven resource actions through `IssuePolicy` automatically. The nested sub-resource controllers (`Issue\TagController`, `Issue\MemberController`) cannot use `authorizeResource` (they don't match the resource naming convention), so they call `$this->authorize('update', $issue)` explicitly at the top of each action — the same policy method, the same ownership check.
