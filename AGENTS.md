# AGENTS.md

## What This Is

Laravel 12 + Inertia.js v2 + Vue 3 app for tilapia fish farm management (ikan nila). Domain language is Indonesian — model names, route names, and variables use Bahasa Indonesia throughout.

Two user roles: **supervisor** (full access) and **operator** (field data input only). Roles enforced via custom `role` middleware alias defined in `bootstrap/app.php`.

## Commands

```bash
composer setup          # Full setup: install, key:generate, migrate, npm install, npm run build
composer dev            # Runs server + queue + pail + vite concurrently
composer test           # Clears config cache, then runs php artisan test

# Formatting (run after editing PHP files)
vendor/bin/pint --dirty --format agent

# Tests
php artisan test --compact                                          # all tests
php artisan test --compact tests/Feature/ExampleTest.php            # single file
php artisan test --compact --filter=testName                        # filter by name
php artisan make:test --phpunit {name}                              # create new test (PHPUnit only)
php artisan make:model ModelName --help                             # check model generation options

# Frontend
npm run build          # production build
npm run dev            # vite dev server
```

## Database

- **Dev**: MySQL (`DB_CONNECTION=mysql`, database `db_pakan_nila`)
- **Tests**: SQLite in-memory (overridden in `phpunit.xml` — tests always use SQLite regardless of `.env`)
- When modifying a column, include ALL previously defined attributes in the migration or they will be dropped.

## Architecture

```
app/
  Models/          8 domain models + User (Kolam is the central entity)
  Http/Controllers  Feature controllers (no base resource abstraction)
  Http/Middleware    HandleInertiaRequests + RoleMiddleware (custom, alias: 'role')
  Services/         ForwardChainingService (expert system for water quality diagnosis)
routes/
  web.php           All routes — role-gated with role:supervisor / role:operator
  auth.php          Laravel Breeze auth routes
  console.php       Console commands
resources/js/
  Pages/            Inertia Vue pages (resolved by name from ./Pages/${name}.vue)
  Layouts/          AuthenticatedLayout, GuestLayout
  Components/       Shared Vue components
database/
  migrations/       12 migration files
  seeders/          UserSeeder (supervisor@nila.com, operator@nila.com — both password: password123)
  factories/        UserFactory only — most models lack factories
```

## Key Conventions

- **PHPUnit only** — no Pest. If you see Pest-style tests, convert them.
- **Only one factory exists** (UserFactory). Models lack factories — check before assuming factory availability.
- **Tests run against SQLite in-memory** even in dev. They do not touch the MySQL database.
- **No API routes** — this is a pure web app. `routes/api.php` does not exist.
- **No CI workflows** — no `.github/` directory exists.
- **`Inertia::render()`** for all server-side routing — no Blade views for pages (only `resources/views/app.blade.php` as the Inertia root template).
- **Ziggy** provides named routes in JS via `route()` function.
- **Flash messages** shared globally via Inertia middleware: `$page.props.flash.success` and `$page.props.flash.warning`.
- **DomPDF** (`barryvdh/laravel-dompdf`) is available for PDF generation.
- **Chart.js** + **vue-chartjs** + **moment** are runtime dependencies (not devDependencies).

## Expert System

`ForwardChainingService` (`app/Services/ForwardChainingService.php`) implements a forward-chaining inference engine. It:
1. Takes `ParameterHarian` (water quality readings)
2. Fuzzifies raw values into linguistic facts (F16–F33)
3. Matches against 12 rules (R01–R12) to produce diagnoses
4. Auto-generates `Tiket` (mitigation tickets) for non-normal diagnoses
5. Logs all inferences to `InferensiLog`

The service also has an external ML API reference (`ML_API_URL` in `.env`, port 8000).

## Gotchas

- `vendor/bin/pint --test --format agent` is incorrect — use `vendor/bin/pint --dirty --format agent` to fix, or just `vendor/bin/pint` to fix all.
- Artisan `make:test` requires `--phpunit` flag — default may create Pest tests.
- All Artisan commands in non-interactive contexts need `--no-interaction`.
- `composer dev` requires `concurrently` (npm package, already in devDependencies).
- If you see "Unable to locate file in Vite manifest", run `npm run build` or ask the user to run `npm run dev`.

## Instruction Files

- `CLAUDE.md` — Laravel Boost guidelines (PHP style, Inertia patterns, testing rules, Pint formatting). These rules apply equally to OpenCode sessions.
- Skills in `.claude/skills/` are referenced in `boost.json` but the skill files don't exist on disk. Activate skills via the `skill` tool when relevant: `laravel-best-practices`, `inertia-vue-development`, `tailwindcss-development`.
