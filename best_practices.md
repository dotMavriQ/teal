# TEAL — coding best practices

Consumed by Qodo Merge's `/improve` tool. Project-specific rules only; general
"write clean code" advice is intentionally omitted. Style/PSR-12 is enforced by
Pint + CI and is out of scope here. Accessibility / Core Web Vitals are
CodeRabbit's lane and are out of scope here.

## Architecture (non-negotiable)

- **No controllers.** Routes point directly to Livewire 3 full-page components.
  Reject any new `app/Http/Controllers` class or controller-style routing.
- **Octane / persistent-worker safety.** Workers are long-lived. No static mutable
  state, no request data captured in singletons or container bindings, no
  unbounded static caches. State must not leak across requests.
- **Single module boundaries.** "Movies & TV Shows" is one module and one data
  structure — do not split it.
- **URL portability.** Production is a subdomain (`teal.dotmavriq.life`) and
  self-hosters may mount elsewhere. Derive every URL/asset from `APP_URL`/
  `ASSET_URL` via `route()`, `asset()`, `@vite`, or `URL::forceRootUrl` — never
  hardcode an absolute path or scheme.

## Multi-tenancy & authorization

- Every model is user-scoped (`user_id` FK, cascade delete). Every query must be
  scoped to `auth()->id()`; a missing scope is a cross-tenant data leak.
- Authorization goes through policies (auto-discovered). New models need a policy
  and new query paths need scoping tests proving user A cannot read user B's data.
- Guard `wire:model` bindings: never bind `user_id`, `metadata_fetched_at`, or
  other non-user-editable fields. Validate all bound properties.

## Performance (code layer)

- Index/list components must eager-load (`with()`) every relationship the view
  touches. No lazy relationship access inside loops.
- Select only needed columns; paginate; avoid `->get()` on unbounded sets.
- Call `resetPage()` when filters/search change.
- Jobs: chunk DB writes (no per-row updates in loops), stay idempotent, and keep
  rate-limit `sleep()`s so retries don't stampede the upstream API.

## External APIs (Saloon)

- One connector per API + request classes; resolve services via `app(Service::class)`.
- Synchronous external HTTP during a web request is a TTFB hazard — queue it.
- Respect cache TTLs; a cache miss must not fan out to N sequential calls on a
  user-facing path.
- Never let API keys reach logs, exceptions, or traces. Null-check response data
  before array access; degrade gracefully when an API is down.

## Data & schema

- Index foreign keys and frequently filtered/sorted columns (`user_id`, `status`,
  and external IDs: `imdb_id`, `mal_id`, `isbn`, `isbn13`). Prefer composite
  `(user_id, status)` indexes matching index-page filters.
- `down()` must reverse `up()`; new columns need sane defaults to avoid rewrite
  locks on large tables.
- Models: configure `$fillable`/`$guarded`; cast dates, enums, ints, floats.

## Conventions

- `declare(strict_types=1)` in every PHP file.
- Status is an enum (`ReadingStatus`, `WatchingStatus`, …) with `label()`/`color()`.
- Forms display dates as **DD/MM/YYYY**; storage is **Y-m-d**.
- Import services return `['imported' => int, 'skipped' => int, 'errors' => array]`.
- Tests (Pest 3): assert real behavior, not just HTTP 200; cover auth scoping.
