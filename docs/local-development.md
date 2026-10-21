# Local development

## Prerequisites

- PHP 8.2 or later with the extensions required by Laravel and PhpSpreadsheet (`dom`, `gd`, `mbstring`, `xml`, `zip`, and related standard extensions).
- Composer 2.
- Node.js 22.12+ (the current Vite 8 toolchain supports Node 20.19+ or 22.12+).
- pnpm 12. The repository pins pnpm through the `packageManager` field.
- A supported database. SQLite is Laravel's default in `config/database.php`; a MySQL-compatible database is also supported by the configuration.

## Initial setup

The current worktree does not contain `.env.example`, so obtain a safe local `.env` from the project owner or create one from Laravel's standard values. Do not copy production credentials into source control.

At minimum, configure:

- `APP_KEY` and `APP_URL`
- the selected `DB_*` connection values
- `MAIL_*` if password reset email needs to be delivered
- session, cache, and queue drivers appropriate for the local database

Then run:

```bash
composer install
pnpm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

`DatabaseSeeder` creates the role records before seeded users, plus initial letter types and a sample template. The seeded administrator credentials are intentionally not reproduced here; inspect or replace the seeder before using it outside a disposable local database.

## Run locally

Use separate terminals:

```bash
php artisan serve
pnpm dev
```

For a production-like asset check:

```bash
pnpm run build
pnpm run build:ssr
```

Composer also defines `composer run dev` and `composer run dev:ssr`, which start the Laravel server, queue worker, logs, and Vite/SSR processes together. Those scripts invoke `npm run`; use the manual pnpm commands above if pnpm is the team standard.

## Quality and maintenance commands

```bash
composer validate --strict
php artisan about
php artisan test
pnpm run format:check
pnpm exec vue-tsc --noEmit
pnpm audit --prod
composer audit --locked
```

Current repository note: the feature test suite and full Vue type check contain existing failures that must be repaired in their own maintenance task. The Vite client and SSR builds pass after the dependency refresh.

## pnpm install policy

`pnpm-workspace.yaml` intentionally controls which third-party dependency build scripts may run. A fresh install may ask for an explicit approval for `vue-demi`. Review that package and use pnpm's approval workflow rather than disabling the policy globally.

## Files generated locally

Do not commit `.env`, local databases, generated storage files, `node_modules`, or production build outputs. The lockfiles are source-controlled dependency artifacts and should be committed.
