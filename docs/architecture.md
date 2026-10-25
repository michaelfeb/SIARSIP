# Architecture and technology

## Foundation

| Layer | Current implementation |
| --- | --- |
| Backend | Laravel 12 on PHP 8.2+ |
| Server/UI bridge | Inertia Laravel 3.5 and Inertia Vue 3.9 |
| Client | Vue 3.5, TypeScript 5.9, Vite 8 |
| Styling | Tailwind CSS 4, Reka UI primitives, Flowbite, and local Vue components |
| Routing in the client | Ziggy route helper shared by Laravel |
| Database | Laravel default is SQLite; Laravel's configured MySQL, MariaDB, PostgreSQL, and SQL Server connections are also available |
| Files | Laravel `public` and custom private `secure` disks |
| Documents | Laravel Excel/PhpSpreadsheet for `.xlsx`; DomPDF for generated PDFs |
| Package managers | Composer 2 for PHP and pnpm 12 for JavaScript |

The versions above are taken from `composer.json`, `composer.lock`, `package.json`, and `pnpm-lock.yaml`. The lockfiles, not only the manifests, should be committed with dependency changes.

## Request and rendering flow

```text
Browser
  → Laravel route and middleware
  → controller / validation / Eloquent model / storage
  → Inertia response with page props
  → Vue page in resources/js/pages
  → Vite client assets (or SSR bundle when SSR is used)
```

Laravel routes are in `routes/web.php`, with authentication routes split into `routes/auth.php` and account settings routes in `routes/settings.php`. `bootstrap/app.php` aliases the custom `role` middleware and attaches Inertia middleware to web requests.

`app/Http/Middleware/HandleInertiaRequests.php` shares the application name, authenticated user, Ziggy route configuration, and sidebar state with every Inertia page.

## Directory map

```text
app/
  Http/Controllers/     request and workflow orchestration
  Http/Middleware/      role and Inertia middleware
  Models/               Eloquent models
  Policies/             model authorization policies
database/
  migrations/           schema history
  seeders/              starter roles, users, and templates
resources/
  js/pages/             Inertia page components, grouped by module
  js/components/        reusable application and UI components
  js/layouts/           authenticated and guest layouts
  views/                Blade roots and PDF templates
routes/                 HTTP route definitions
storage/app/public/     publicly linked assets
    storage/app/secure_storage/
                        private application documents and generated replies
docs/                   this documentation
```

## Frontend composition

`resources/js/app.ts` creates the Inertia Vue application. It resolves page names such as `BerkasSidangNol/Index` to files in `resources/js/pages/BerkasSidangNol/Index.vue` and installs Ziggy for the global `route()` helper.

Authenticated pages normally use `resources/js/layouts/AppLayout.vue`, which delegates to the sidebar layout. The sidebar menu is currently assembled in `resources/js/components/AppSidebarMenu.ts` from the authenticated user's numeric role ID.

`vite.config.ts` configures Laravel's Vite entry points, Tailwind, Vue compiler type resolution, aliases for `@` and `ziggy-js`, and esbuild CSS minification. The latter preserves production builds while handling CSS emitted by a current third-party table component.

## Storage model

| Disk | Physical root | Intended content | Exposure |
| --- | --- | --- | --- |
| `public` | `storage/app/public` | template `.doc/.docx` files and carousel images | `public/storage` symlink and application routes |
| `secure` | `storage/app/secure_storage` | Sidang Nol files, Cuti Akademik evidence/final PDFs, and retained unreferenced legacy files | authorized application routes only |

The `secure` disk is configured as private in `config/filesystems.php`. File access is still implemented by application routes, so future changes must retain explicit authorization checks before returning a file.

Legacy Berkas Persuratan attachments intentionally remain on this disk after the module and its database records are retired. They have no active application route or database reference and must not be removed by routine cleanup.

## Letter foundation

Future academic-letter services share `letter_types`, `letters`,
`letter_attachments`, `letter_routes`, and `letter_logs`. `letters` uses UUID
identifiers and contains common lifecycle, applicant snapshot, manual-number,
and final-PDF fields. Service-specific form data belongs in its JSON `meta`
column.

Cuti Akademik is the first active service on this foundation. Its module owns
its routes, validation, approval transitions, and PDF renderer. The shared
`LetterAttachmentService` owns only private attachment persistence/cleanup;
the shared frontend upload section owns only file-selection presentation.
Both remain separate from Berkas Sidang Nol and Template Surat.

## Architecture boundaries for new work

1. Put HTTP validation and workflow decisions in the owning controller or a dedicated request/service when the logic grows.
2. Keep schema changes additive through migrations; do not edit historical migrations that may already have run in deployed databases.
3. Keep the route, controller, Vue page, model/migration, and this documentation synchronized for a module change.
4. Treat a role ID as persisted business data. Prefer named constants or permission records before adding more role-specific behavior.
5. Store private student documents only on the `secure` disk; public content belongs on the `public` disk.
