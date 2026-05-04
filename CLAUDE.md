# taskerz-backend

Laravel 11 REST API powering `taskerz-fronted`. PHP 8.2/8.3. See `../CLAUDE.md` for ecosystem context.

## Stack

- **Framework**: Laravel 11 (new bootstrap-style config in `bootstrap/app.php`, no `app/Http/Kernel.php`)
- **Auth**: `laravel/sanctum` for admins; custom token check for taskers
- **Image storage**: `cloudinary-labs/cloudinary-laravel` + `intervention/image`
- **Email**: `resend/resend-laravel` (driver `resend`, from `no-reply@taskers.rw`)
- **DB**: MySQL (`taskers` schema locally; deployed elsewhere)
- **Deploy**: Andasy (`andasy.hcl`, `.andasy/`), nginx + php-fpm via `Dockerfile`

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

There are also debug routes (`/env-test`, `/check-upload-limits`, `/check-test-cloudinary`) that should not exist in production.

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
- Use `FormRequest` validation when possible (see `app/Http/Requests/`); inline `Validator::make` is also used.
- New CRUD endpoints: add the route under the right middleware group in `api.php`, the controller method, and any FormRequest. Then add a matching service in `taskerz-fronted/src/lib/services/`.
- File uploads: use Cloudinary (config in `config/cloudinary.php`, env `CLOUDINARY_*`).
- Email: `Mail::to(...)->send(new SomeMailable())`; Resend handles delivery.

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
- The `.env` checked in to the working tree contains live Cloudinary + Resend credentials. Treat with caution; do not commit.
- `users.role` is added by a later migration (`add_role_to_users`) — fresh databases must run all migrations or admin gating will break.
- `soft deletes` are enabled but not all queries scope them — verify `withTrashed()`/default scope behavior when filtering.
