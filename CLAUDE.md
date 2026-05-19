# taskerz-backend

Laravel 11 REST API powering `taskerz-fronted`. PHP 8.2/8.3. See `../CLAUDE.md` for ecosystem context.

## Stack

- **Framework**: Laravel 11 (new bootstrap-style config in `bootstrap/app.php`, no `app/Http/Kernel.php`)
- **Auth**: `php-open-source-saver/jwt-auth` — single `api` JWT guard for admins, taskers and customers. `laravel/sanctum` is still required by composer but no longer used at runtime; the `personal_access_tokens` table is kept as a safety net.
- **Image storage**: `cloudinary-labs/cloudinary-laravel` + `intervention/image`
- **Email**: `resend/resend-laravel` (driver `resend`, from `no-reply@taskers.rw`) — every send is queued, retried 3× with 1m/5m/15m backoff, and dispatched through `App\Services\EmailNotificationService`. Knobs in `config/notifications.php`.
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
    TaskerMatchingService.php       # location/profession/skill scoring, used by recommendations
    EmailNotificationService.php    # single dispatch point for every transactional email
  Mail/                 # One Mailable per email type (EmailVerificationMail, WelcomeMail,
                        # TaskerWelcomeMail, PasswordResetMail, TaskerApprovedMail,
                        # TaskerRejectedMail, TaskRequestSubmittedMail, AdminNewTaskRequestMail,
                        # TaskAssignedTo{Tasker,Requester}Mail, TaskRequest{Approved,Completed,Cancelled}Mail).
                        # All implement ShouldQueue + use Concerns\SendsAsTransactional.
config/
  jwt.php               # JWT TTLs, blacklist, etc. Published from jwt-auth.
  notifications.php     # Admin email recipients, support contact, frontend URL,
                        # mail queue tuning, verification-code + password-reset TTLs.
routes/
  api.php               # ALL API routes live here — single source of truth
database/
  migrations/           # 2026_05_11_120000_merge_taskers_into_users.php is the
                        # one-shot merge migration; irreversible.
```

## Routing rules (`routes/api.php`)

Four tiers — keep new endpoints in the right group:

1. **Public reads** — under `Route::prefix('open')`: `/api/open/{faqs,testimonials,services,taskers,tasks,sub-tasks,...}`. No auth.
2. **Public writes** — `POST /api/login`, `POST /api/register` (customer), `POST /api/task-requests`, `POST /api/taskers` (tasker register), plus auth helpers: `POST /api/auth/{verify-code,request-verification,forgot-password,reset-password}` and the legacy tasker aliases `POST /api/taskers/{request-verification,verify-code}`.
3. **Authenticated (any role)** — `Route::middleware('auth:api')` for `GET /api/auth/me`, `POST /api/auth/refresh`, `POST /api/auth/logout` (+ legacy `GET /api/logout`).
4. **Tasker** — `Route::middleware(['auth:api', 'role:tasker'])->prefix('tasker')`: dashboard + profile + `/userdata` (which proxies to `AuthController@me`).
5. **Admin** — `Route::middleware(['auth:api', 'admin'])`: full CRUD for tasks, sub-tasks, task-requests, taskers (admin management), services, faqs, testimonials, plus `/recommendations/*` and `/analytics/*`. Task-request lifecycle: `POST /api/task-requests/{id}/{approve,reject,assign,complete,cancel}`. Tasker vetting: `PUT /api/taskers/{id}/{approve,reject}` (reject accepts an optional `reason`).

`route:cache` succeeds — keep `routes/api.php` closure-free. The `role:tasker` argument syntax is route-cache safe (parsed at compile time). The deploy boots run `config:cache` + `route:cache` + `view:cache` via `.andasy/scripts/caches.sh`.

## Auth model

Single JWT-based flow for every actor; the unified `users` table has three roles:

- `admin` — seeded; logs in at `POST /api/login` with email + password → JWT. Admins skip the email-verification gate.
- `tasker` — self-registers at `POST /api/taskers` with name/email/password/phone/profession (+ optional fields). Backend creates a row with `role='tasker'`, `status='pending'`, `email_verified_at=NULL` and emails a **6-digit numeric** `verification_code` (TTL `VERIFICATION_CODE_TTL_MINUTES`, default 30). The endpoint returns the user only — **no token**. Calling `POST /api/login` for an unverified tasker returns `403 { requires_verification: true, email }` AND auto-issues a fresh code. After `POST /api/auth/verify-code` (or the legacy `/api/taskers/verify-code` alias) succeeds, `email_verified_at` is set, a JWT is issued, and a welcome email is queued. Status stays `pending` until an admin runs `PUT /api/taskers/{id}/approve` — which also queues an approval email; `reject` accepts an optional `reason` and emails the tasker.
- `user` (customer) — `POST /api/register` creates a `role='user'` account with `email_verified_at=NULL` and emails a 6-digit code. **No token is issued** until the customer calls `POST /api/auth/verify-code` (same generic endpoint taskers use). `POST /api/login` for an unverified customer also returns `403 { requires_verification: true, email }`. A welcome email fires on first verification.

Password reset works for every role: `POST /api/auth/forgot-password { email }` → emailed reset link → `POST /api/auth/reset-password { email, token, password, password_confirmation }`. Both endpoints **always return 200** (no email enumeration). Reset links are signed by Laravel's `Password` broker and expire after `PASSWORD_RESET_TTL_MINUTES` (default 60). `User::sendPasswordResetNotification` is overridden to route through `EmailNotificationService`.

Verification codes live on `users.verification_code` with `users.verification_code_sent_at` for TTL enforcement (added in migration `2026_05_15_120000_add_verification_code_sent_at_to_users_table`). Always 6 numeric digits — the frontend input expects digits and we generate via `random_int(0, 999999)`.

`User::isAdmin()`, `User::isTasker()`, `User::isCustomer()` check the role. Read the authenticated user inside controllers via `auth('api')->user()` (never `$request->user()` from session-based auth). `AdminMiddleware` and `RoleMiddleware` both call `auth('api')->user()` and return JSON `401` / `403`.

JWT settings live in `.env` (`JWT_SECRET`, `JWT_TTL`, `JWT_REFRESH_TTL`) and `config/jwt.php`. Refresh invalidates the previous token immediately — clients should replace it on the next request.

## Domain

```
Task ──< SubTask ──< TaskRequest ──┬── User (role='user',   customer_id) — submitter
                                   └── User (role='tasker', user_id)    — assignee
```

`tasks`, `sub_tasks`, `task_requests` all use `softDeletes()`. `task_requests.status` is an enum: `pending|approved|cancelled|completed`. `users.skills` is JSON-cast (stored as text).

`task_requests` carries **two** nullable user FKs — keep them straight:

- `customer_id` (added in migration `2026_05_19_120000_add_customer_id_to_task_requests`) — set by `TaskRequestController::store()` when `auth('api')->user()->isCustomer()` is true. Immutable afterwards; nothing else should write to it. Read via `TaskRequest::customer()` or `User::customerRequests()` / `completedCustomerRequests()`.
- `user_id` (formerly `tasker_id` — renamed in the merge migration `2026_05_11_120000`) — the assigned tasker. Set by `TaskRequest::assignToTasker($taskerId)`, nulled when the row is created. Read via `TaskRequest::tasker()` (the relation name was kept for historical reasons; the column is now `user_id`) or `User::taskRequests()` / `assignedTasks()` / `completedTaskRequests()`. **All tasker-side queries (`TaskerDashboardController`, analytics earnings, recommendations) match by `user_id`** — don't accidentally reuse `customer_id`.

The `2026_05_19_120000` migration backfills existing rows: any `user_id` that pointed at a `role='user'` was moved to `customer_id`; remaining nulls were filled by matching `task_requests.email` against `users.email WHERE role='user'`.

`POST /api/task-requests/{id}/assign` still accepts a `tasker_id` body param — the value is just a `users.id` with `role='tasker'` (validated via `Rule::exists('users','id')->where('role','tasker')`). Don't rename the param without grepping the frontend.

The admin `GET /api/clients/{id}` response eager-loads the customer's recent submissions; Laravel serializes the relation under the snake_case key `customer_requests` (not `task_requests`). The Clients index uses `withCount(['customerRequests as total_requests', 'completedCustomerRequests as completed_requests'])`.

## Conventions

- Responses: `{ success: bool, message?: string, data?: ... }` is the prevailing shape; some older endpoints just return the bare model. Match the surrounding controller.
- **Index endpoints paginate** (`Tasker`, `Task`, `SubTask`, `TaskRequest::index`): wrap with `{ success, data: $paginator->items(), meta: { current_page, last_page, per_page, total, from, to } }`. Use `->paginate($request->integer('per_page', 20))`. Add `search` / `status` query params to the controller, not in JS. Use `$request->filled(...)` (treats empty string as missing), not `$request->has(...)`.
- Use `FormRequest` validation when possible; inline `Validator::make` is also used.
- New CRUD endpoints: add the route under the right middleware group in `api.php`, the controller method, and any FormRequest. Then add a matching service in `taskerz-fronted/src/lib/services/`.
- File uploads: use Cloudinary (config in `config/cloudinary.php`, env `CLOUDINARY_*`). Currently synchronous in the request path.
- **All transactional email goes through `App\Services\EmailNotificationService`** — never call `Mail::to(...)->send(...)` directly from a controller. The service handles recipient validation, structured logging (`Queued $type email` / `Failed to dispatch $type email` to the default log channel with `mailable`, `recipient`, `user_id`/`task_request_id` context), and try/catch around dispatch so a Resend outage never breaks the originating user-facing action. Permanent failures (queue retries exhausted) are logged from `Mailable::failed()` via the `SendsAsTransactional` trait.
- **Add new emails by**: (1) creating `app/Mail/SomethingMail.php` that `implements ShouldQueue` + uses the `Concerns\SendsAsTransactional` trait (don't forget `$this->buildQueueable()` in the constructor), (2) creating the blade template under `resources/views/emails/` using `@component('emails.layouts.base', ['title' => '…'])` for shared chrome, (3) adding a `sendXyz(...)` method on `EmailNotificationService`, (4) calling that method from the controller. Tries/backoff/queue are inherited from `config('notifications.queue')`.
- **Email is queued** — `QUEUE_CONNECTION=database`. Locally run `php artisan queue:work` in a second shell to deliver verification and lifecycle emails during testing. Production runs the worker via supervisor.
- For tasker admin CRUD (list/show/update/destroy/approve/reject), use `User::taskers()` scope — every method on `TaskerController` already filters by role. Don't query `User` directly without scoping.
- For tasker dashboard queries, the authenticated tasker is `auth('api')->user()`. Their `TaskRequest`s join by `user_id` (the renamed column). For customer "my requests" queries, use `customer_id` via `User::customerRequests()` — see Domain section above.

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
- `.env` is gitignored. Production `.env` lives on Andasy and must be updated there. Keep `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=warning` in production. Production also needs `JWT_SECRET` set or every request 500s. Mail-related keys it needs: `RESEND_API_KEY`, `MAIL_MAILER=resend`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `ADMIN_NOTIFICATION_EMAILS` (comma-separated; falls back to `users WHERE role=admin` if empty), `SUPPORT_EMAIL`, `SUPPORT_PHONE`, `FRONTEND_URL`. Optional: `VERIFICATION_CODE_TTL_MINUTES`, `PASSWORD_RESET_TTL_MINUTES`, `MAIL_QUEUE_*`.
- `soft deletes` are enabled on tasks/sub_tasks/task_requests but not all queries scope them — verify `withTrashed()`/default scope behavior when filtering.
- Production has **OPcache `validate_timestamps=0`** (set by `.andasy/scripts/00-opcache.sh`). Code changes only take effect on a fresh image rebuild — don't expect hotpatching by editing files in a running container.
- PHP-FPM pool sizes come from Andasy env vars (`PHP_PM_*`), not `docker/www.conf` (which is dead config for the Andasy path).
