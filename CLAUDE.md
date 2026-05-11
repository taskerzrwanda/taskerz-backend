# taskerz-backend

Laravel 11 REST API powering `taskerz-fronted`. PHP 8.2/8.3. See `../CLAUDE.md` for ecosystem context.

## Stack

- **Framework**: Laravel 11 (new bootstrap-style config in `bootstrap/app.php`, no `app/Http/Kernel.php`)
- **Auth**: `php-open-source-saver/jwt-auth` — single `api` JWT guard for admins, taskers and customers. `laravel/sanctum` is still required by composer but no longer used at runtime; the `personal_access_tokens` table is kept as a safety net.
- **Image storage**: `cloudinary-labs/cloudinary-laravel` + `intervention/image`
- **Email**: `resend/resend-laravel` (driver `resend`, from `no-reply@taskers.rw`) — sends are queued
- **Queue**: `database` driver; worker runs via supervisor (`.andasy/supervisor/conf.d/queue.conf`)
- **DB**: MySQL (`taskers` schema locally; deployed elsewhere)
- **Deploy**: Andasy (`andasy.hcl`, `.andasy/`), nginx + php-fpm + queue worker under supervisord. The `Dockerfile` + `docker/` dir are an alternate path used for non-Andasy local Docker dev only.

## Layout

```
app/
  Http/
    Controllers/        # AuthController, TaskerRegistrationController (signup/verify),
                        # TaskerController (admin CRUD), TaskerDashboardController, etc.
    Middleware/
      AdminMiddleware.php   # alias: 'admin' — shortcut for role:admin via auth('api')
      RoleMiddleware.php    # alias: 'role' — usage: ->middleware('role:tasker,admin')
    Requests/           # LoginRequest, RegisterRequest, RegisterTaskerRequest,
                        # StoreUserRequest, UpdateUserRequest
  Models/               # Eloquent: User (absorbs former Tasker fields), Task, SubTask,
                        # TaskRequest, Service, Faq, Testimonial
  Services/
    TaskerMatchingService.php   # location/profession/skill scoring, used by recommendations
  Mail/                 # TaskerVerificationMail, TaskerEmailSender
config/
  jwt.php               # JWT TTLs, blacklist, etc. Published from jwt-auth.
routes/
  api.php               # ALL API routes live here — single source of truth
database/
  migrations/           # 2026_05_11_120000_merge_taskers_into_users.php is the
                        # one-shot merge migration; irreversible.
```

## Routing rules (`routes/api.php`)

Four tiers — keep new endpoints in the right group:

1. **Public reads** — under `Route::prefix('open')`: `/api/open/{faqs,testimonials,services,taskers,tasks,sub-tasks,...}`. No auth.
2. **Public writes** — `POST /api/login`, `POST /api/register` (customer), `POST /api/task-requests`, `POST /api/taskers` (tasker register), `POST /api/taskers/{request-verification,verify-code}`.
3. **Authenticated (any role)** — `Route::middleware('auth:api')` for `GET /api/auth/me`, `POST /api/auth/refresh`, `POST /api/auth/logout` (+ legacy `GET /api/logout`).
4. **Tasker** — `Route::middleware(['auth:api', 'role:tasker'])->prefix('tasker')`: dashboard + profile + `/userdata` (which proxies to `AuthController@me`).
5. **Admin** — `Route::middleware(['auth:api', 'admin'])`: full CRUD for tasks, sub-tasks, task-requests, taskers (admin management), services, faqs, testimonials, plus `/recommendations/*` and `/analytics/*`.

`route:cache` succeeds — keep `routes/api.php` closure-free. The `role:tasker` argument syntax is route-cache safe (parsed at compile time). The deploy boots run `config:cache` + `route:cache` + `view:cache` via `.andasy/scripts/caches.sh`.

## Auth model

Single JWT-based flow for every actor; the unified `users` table has three roles:

- `admin` — seeded; logs in at `POST /api/login` with email + password → JWT.
- `tasker` — self-registers at `POST /api/taskers` with name/email/password/phone/profession (+ optional fields). Backend creates a row with `role='tasker'`, `status='pending'`, `email_verified_at=NULL` and emails a 6-char `verification_code`. The endpoint returns the user only — **no token**. Calling `POST /api/login` for an unverified tasker returns `403 { requires_verification: true, email }`. After `POST /api/taskers/verify-code` succeeds, `email_verified_at` is set and a JWT is issued; from then on the tasker logs in via the standard `POST /api/login`. Status stays `pending` until an admin runs `PUT /api/taskers/{id}/approve`.
- `user` (customer) — `POST /api/register` creates a `role='user'` account and issues a JWT immediately.

`User::isAdmin()`, `User::isTasker()`, `User::isCustomer()` check the role. Read the authenticated user inside controllers via `auth('api')->user()` (never `$request->user()` from session-based auth). `AdminMiddleware` and `RoleMiddleware` both call `auth('api')->user()` and return JSON `401` / `403`.

JWT settings live in `.env` (`JWT_SECRET`, `JWT_TTL`, `JWT_REFRESH_TTL`) and `config/jwt.php`. Refresh invalidates the previous token immediately — clients should replace it on the next request.

## Domain

```
Task ──< SubTask ──< TaskRequest >── User (role='tasker', nullable, set on assignment)
```

`tasks`, `sub_tasks`, `task_requests` all use `softDeletes()`. `task_requests.status` is an enum: `pending|approved|cancelled|completed`. The FK column is `task_requests.user_id` (not `tasker_id` — it was renamed). The Eloquent relation is intentionally still called `TaskRequest::tasker()` (returns a `User`) so existing `with('tasker')` callsites and frontend payload keys keep working. `users.skills` is JSON-cast (stored as text).

`POST /api/task-requests/{id}/assign` still accepts a `tasker_id` body param — the value is just a `users.id` with `role='tasker'` (validated via `Rule::exists('users','id')->where('role','tasker')`). Don't rename the param without grepping the frontend.

## Conventions

- Responses: `{ success: bool, message?: string, data?: ... }` is the prevailing shape; some older endpoints just return the bare model. Match the surrounding controller.
- **Index endpoints paginate** (`Tasker`, `Task`, `SubTask`, `TaskRequest::index`): wrap with `{ success, data: $paginator->items(), meta: { current_page, last_page, per_page, total, from, to } }`. Use `->paginate($request->integer('per_page', 20))`. Add `search` / `status` query params to the controller, not in JS. Use `$request->filled(...)` (treats empty string as missing), not `$request->has(...)`.
- Use `FormRequest` validation when possible; inline `Validator::make` is also used.
- New CRUD endpoints: add the route under the right middleware group in `api.php`, the controller method, and any FormRequest. Then add a matching service in `taskerz-fronted/src/lib/services/`.
- File uploads: use Cloudinary (config in `config/cloudinary.php`, env `CLOUDINARY_*`). Currently synchronous in the request path.
- **Email is queued** — `TaskerVerificationMail` and `TaskerEmailSender` `implements ShouldQueue`. `QUEUE_CONNECTION=database`. Locally run `php artisan queue:work` in a second shell to deliver verification emails during testing.
- For tasker admin CRUD (list/show/update/destroy/approve/reject), use `User::taskers()` scope — every method on `TaskerController` already filters by role. Don't query `User` directly without scoping.
- For tasker dashboard queries, the authenticated tasker is `auth('api')->user()`. Their `TaskRequest`s join by `user_id` (the renamed column).

## Local dev

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan jwt:secret
# create MySQL db `taskers`, set DB_USERNAME/PASSWORD
php artisan migrate --seed
php artisan serve            # http://localhost:8000
php artisan queue:work       # second shell — required for verification email to fire
```

Default seeded admin: `taskerzrwanda@gmail.com` / `mb1234567`. Seeded taskers all share the password from `TASKER_SEED_PASSWORD` in `.env` (default `TaskerSeed!2026`).

## Gotchas

- `bootstrap/app.php` registers only two middleware aliases (`admin`, `role`) plus the JSON `redirectGuestsTo` shim. The old `tasker.auth` alias and the no-op `BlockBrowserAccess` are gone — don't reintroduce them; use `role:tasker` instead.
- The legacy `taskers` table has been merged into `users` (migration `2026_05_11_120000_merge_taskers_into_users`). The migration is **irreversible** (`down()` throws). Don't try to `migrate:rollback` past it on production — restore from backup if you must.
- The merge migration handles email collisions (same email in `users` and `taskers`) by upgrading the existing user with tasker fields. Pre-verify taskers with `NULL` password get bcrypt(`TASKER_SEED_PASSWORD`) so they can log in.
- `users.password` was made nullable during the merge to absorb legacy taskers — a future migration will re-enforce NOT NULL once `SELECT COUNT(*) FROM users WHERE password IS NULL` is 0.
- `.env` is gitignored. Production `.env` lives on Andasy and must be updated there. Keep `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=warning` in production. Production also needs `JWT_SECRET` set or every request 500s.
- `soft deletes` are enabled on tasks/sub_tasks/task_requests but not all queries scope them — verify `withTrashed()`/default scope behavior when filtering.
- Production has **OPcache `validate_timestamps=0`** (set by `.andasy/scripts/00-opcache.sh`). Code changes only take effect on a fresh image rebuild — don't expect hotpatching by editing files in a running container.
- PHP-FPM pool sizes come from Andasy env vars (`PHP_PM_*`), not `docker/www.conf` (which is dead config for the Andasy path).
