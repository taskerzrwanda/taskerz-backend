# taskerz-backend

Laravel 11 REST API powering `taskerz-fronted`. PHP 8.2/8.3. See `../CLAUDE.md` for ecosystem context.

## Stack

- **Framework**: Laravel 11 (new bootstrap-style config in `bootstrap/app.php`, no `app/Http/Kernel.php`)
- **Auth**: `laravel/sanctum` for admins; custom token check for taskers
- **Image storage**: `cloudinary-labs/cloudinary-laravel` + `intervention/image`
- **Email**: `resend/resend-laravel` (driver `resend`, from `no-reply@taskers.rw`) — sends are queued
- **Queue**: `database` driver; worker runs via supervisor (`.andasy/supervisor/conf.d/queue.conf`)
- **DB**: MySQL (`taskers` schema locally; deployed elsewhere)
- **Deploy**: Andasy (`andasy.hcl`, `.andasy/`), nginx + php-fpm + queue worker under supervisord. The `Dockerfile` + `docker/` dir are an alternate path used for non-Andasy local Docker dev only.

## Layout

```
app/
  Http/
    Controllers/        # one controller per resource (Task, SubTask, TaskRequest, Tasker, ...)
    Middleware/
      AdminMiddleware.php       # alias: 'admin' — requires user.role === 'admin'
      TaskerAuthMiddleware.php  # alias: 'tasker.auth' — checks X-Access-Token header
      BlockBrowserAccess.php    # registered on api group, but currently a passthrough (no-op)
    Requests/           # Form requests for auth (Login, Register, StoreUser, UpdateUser)
    Resources/          # UserResource only
  Models/               # Eloquent: User, Tasker, Task, SubTask, TaskRequest, Service, Faq, Testimonial
  Services/
    TaskerMatchingService.php   # location/profession/skill scoring, used by recommendations
  Mail/                 # TaskerVerificationMail, TaskerEmailSender
  Providers/
routes/
  api.php               # ALL API routes live here — single source of truth
  web.php, console.php  # essentially unused
database/
  migrations/           # numbered; mix of canonical + add_* migrations
  seeders/  factories/
  laravel_react.sql     # SQL dump (reference, not auto-loaded)
docker/                 # nginx.conf, www.conf, start.sh used by Dockerfile
```

## Routing rules (`routes/api.php`)

Three tiers — keep new endpoints in the right group:

1. **Public reads** — under `Route::prefix('open')`: `/api/open/{faqs,testimonials,services,taskers,tasks,sub-tasks,...}`. No auth.
2. **Public writes (limited)** — `POST /api/task-requests`, `POST /api/taskers`, `POST /api/taskers/{request-verification,verify-code}`, `POST /api/login`, `POST /api/register`.
3. **Tasker auth** — `Route::middleware(['tasker.auth'])->prefix('tasker')`: dashboard + profile. Header: `X-Access-Token`.
4. **Admin** — `Route::middleware(['auth:sanctum', 'admin'])`: full CRUD for tasks, sub-tasks, task-requests, taskers, services, faqs, testimonials, plus `/recommendations/*` and `/analytics/*`.

`route:cache` succeeds — keep `routes/api.php` closure-free (use controller methods, never `Route::get('/x', function () { ... })`). The deploy boots run `config:cache` + `route:cache` + `view:cache` via `.andasy/scripts/caches.sh`.

## Auth model

- **Admin**: standard Sanctum personal access token. Login at `POST /api/login` returns `{ user, token }`. `User::isAdmin()` checks `role === 'admin'`. The `AdminMiddleware` returns `401` (no user) or `403` (not admin), JSON body always.
- **Tasker**: opaque `access_token` stored on the `taskers` row, set during `verify-code`. Frontend sends `X-Access-Token: <value>`. `TaskerAuthMiddleware` looks up the tasker and `$request->merge(['authenticated_tasker' => $tasker])`. Read it via `$request->input('authenticated_tasker')` in controllers.

## Domain

```
Task ──< SubTask ──< TaskRequest >── Tasker (nullable, set on assignment)
```

`tasks`, `sub_tasks`, `task_requests` all use `softDeletes()`. `task_requests.status` is an enum: `pending|approved|cancelled|completed`. `taskers.skills` is JSON-cast (stored as text in MySQL).

## Conventions

- Responses: `{ success: bool, message?: string, data?: ... }` is the prevailing shape; some older endpoints just return the bare model. Match the surrounding controller.
- **Index endpoints paginate** (`Tasker`, `Task`, `TaskRequest::index`): wrap with `{ success, data: $paginator->items(), meta: { current_page, last_page, per_page, total, from, to } }`. Use `->paginate($request->integer('per_page', 20))`. Add `search` / `status` query params to the controller, not in JS.
- Use `FormRequest` validation when possible (see `app/Http/Requests/`); inline `Validator::make` is also used.
- New CRUD endpoints: add the route under the right middleware group in `api.php`, the controller method, and any FormRequest. Then add a matching service in `taskerz-fronted/src/lib/services/`.
- File uploads: use Cloudinary (config in `config/cloudinary.php`, env `CLOUDINARY_*`). Currently synchronous in the request path.
- **Email is queued** — `TaskerVerificationMail` and `TaskerEmailSender` `implements ShouldQueue`, so `Mail::to(...)->send($mailable)` enqueues a job rather than blocking. New mailables should follow the same pattern. `QUEUE_CONNECTION=database` (jobs table). The worker runs in production via `.andasy/supervisor/conf.d/queue.conf` (`php artisan queue:work`); locally run `php artisan queue:work` in a second shell when testing mail.
- In `tasker.auth` controllers, read the authenticated tasker via `$request->input('authenticated_tasker')` (set by the middleware) — don't re-query by `access_token` header; the lookup already happened.

## Local dev

```bash
composer install
cp .env.example .env && php artisan key:generate
# create MySQL db `taskers`, set DB_USERNAME/PASSWORD
php artisan migrate
php artisan serve   # http://localhost:8000
```

## Gotchas

- `bootstrap/app.php` calls `withMiddleware()` **twice** — Laravel merges them, but be aware when editing.
- `BlockBrowserAccess` middleware is wired on the api group but its `handle()` just passes through. Don't assume browser blocking is in place.
- `.env` is gitignored. The local file you see is dev-only — the deployed `.env` lives on Andasy and must be updated there. Keep `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=warning` in production.
- `users.role` is added by a later migration (`add_role_to_users`) — fresh databases must run all migrations or admin gating will break.
- `soft deletes` are enabled but not all queries scope them — verify `withTrashed()`/default scope behavior when filtering.
- Production has **OPcache `validate_timestamps=0`** (set by `.andasy/scripts/00-opcache.sh`). Code changes only take effect on a fresh image rebuild — don't expect hotpatching by editing files in a running container.
- `taskers.access_token` is indexed; PHP-FPM pool sizes come from Andasy env vars (`PHP_PM_*`), not `docker/www.conf` (which is dead config for the Andasy path).
