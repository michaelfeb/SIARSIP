# PRD 002 — Legacy Retirement and Template Surat Decoupling

**Status:** Implemented — pending production deployment
**Created:** 23 October 2026  
**Plan status:** Implemented
**Plan version:** 1  
**Mode:** Full stack

## Overview

Retire the unused Berkas Persuratan and old Jenis Surat modules. Their database
data is intentionally deleted because it is not trustworthy production data.
Physical legacy attachments remain untouched on the private storage disk.

Template Surat remains a retained module, but becomes independent from the old
`jenis_surat` table before that table is dropped. Berkas Sidang Nol is outside
this work and must remain unchanged.

## Background

The former generic letter workflow (`berkas_persuratan`) and its
`jenis_surat` master are no longer the application direction. Future services
will use the Letter Types and Letters foundation in PRD 003.

`template_surat.jenis_surat_id` is currently a required foreign key, and the
Dashboard currently queries `berkas_persuratan`. Both dependencies must be
removed before legacy tables are dropped.

## User stories

- As an administrator, I want obsolete modules and invalid data removed so the
  application no longer presents old workflows.
- As a Template Surat administrator, I want templates managed independently of
  a retired letter type.
- As a Sidang Nol user, I want the existing workflow and documents untouched.

## Scope

- Decouple `template_surat` from `jenis_surat`.
- Remove Berkas Persuratan and Jenis Surat backend, frontend, navigation,
  exports, policies, seeders, and database tables.
- Delete legacy `notes` where `jenis_berkas = '1'`.
- Preserve every file on `storage/app/secure_storage` without alteration.
- Return `410 Gone` for legacy web and API URLs.
- Remove all legacy Persuratan Dashboard metrics, charts, filters, and queries.
- Send students to Berkas Sidang Nol; permit every non-student role to use
  Dashboard.
- Update affected application documentation.

## Out of scope

- Deleting, moving, renaming, or cataloguing legacy attachment files.
- Changing Berkas Sidang Nol schema, routes, documents, decisions, or UI.
- Building a new letter service, Letter Type UI, approval-chain UI, Markdown
  notes UI, or the new Letters foundation.
- Migrating Sidang Nol records to a new letters table.

## Acceptance criteria

- [x] Retired Berkas Persuratan, API, and Jenis Surat URLs return HTTP `410`.
- [x] `berkas_persuratan` and `jenis_surat` are dropped after dependencies are
  removed.
- [x] Existing Template Surat records, files, downloads, and publication state
  work without `jenis_surat_id`.
- [x] All legacy `notes` rows are deleted while Sidang Nol notes remain.
- [x] The retirement does not write, delete, move, or rename secure-disk files.
- [x] Dashboard contains no query against `berkas_persuratan`.
- [x] Sidang Nol remains functional.

## Functional requirements

1. Template Surat must no longer load, validate, display, save, search by, or
   relate to `jenis_surat`.
2. Existing template records, public template files, landing-page cards,
   management pages, and downloads must continue to work.
3. Delete `notes` rows with `jenis_berkas = '1'`, then drop the two legacy
   tables.
4. Do not invoke any storage cleanup during retirement.
5. Remove every active route, controller, page, policy, export, sidebar item,
   and seeder for the retired modules.
6. Replace former `/berkas-persuratan...`,
   `/api/berkas-persuratan...`, and `/jenis-surat...` entry points with `410`.
7. Dashboard is independent of legacy Persuratan data. Students who open it
   redirect to Sidang Nol; non-students may access it.

## Non-functional requirements

- Use forward-only migrations; never edit historical migrations.
- Treat the destructive migration as unrecoverable through Laravel rollback.
  Restoration uses a verified production database backup.
- Retained legacy files stay private and become intentionally unreferenced by
  the application after route retirement.
- Preserve the current Laravel, Inertia, Vue, TypeScript, and Tailwind stack.

## Edge cases

| Scenario | Expected behavior |
| --- | --- |
| A legacy file uses a generic secure-storage folder | It remains untouched; no retirement operation mutates storage. |
| A user opens a retired URL | The application returns `410 Gone`, not a redirect or accidental 404. |
| An existing template is edited after decoupling | It saves without `jenis_surat_id` and retains its document unless replaced. |
| A student opens Dashboard | The student is redirected to Berkas Sidang Nol. |
| A non-student role opens Dashboard | It is accessible without only allowing the former fixed role IDs. |
| Recovery is needed after a table drop | Restore from the pre-deployment database backup. |

## Existing-system findings

| Area | Evidence | Implementation implication |
| --- | --- | --- |
| Template Surat | `template_surat.jenis_surat_id` is required; controller and Vue form load `JenisSurat` | Remove relation usage before dropping `jenis_surat`. |
| Legacy files | `BerkasPersuratanController` uses private `secure` folders | No storage operation belongs in retirement. |
| Sidang Nol files | Uses separate `berkas_sidang_nol/...` paths | Remains untouched. |
| Shared notes | `jenis_berkas = 1` is Persuratan; `2` is Sidang Nol | Delete only legacy note rows. |
| Dashboard | Controller and page query and present Persuratan data | Remove this path before table removal. |
| Routes | `routes/web.php` maps legacy handlers directly | Replace with explicit `410` fallbacks. |

## Design and architecture

### Ownership

- Template Surat owns retained template behavior.
- Retirement migrations own legacy database deletion and table removal.
- Top-level fallback routes own the `410 Gone` response.
- Dashboard retains only valid Sidang Nol summary behavior until new services
  are implemented.

### Release flow

```text
Verified production database backup
→ decouple and verify Template Surat
→ delete legacy notes and drop legacy tables
→ remove legacy code, menu, and Dashboard dependencies
→ add 410 retirement fallbacks
→ complete regression verification
```

### Decisions

| Decision | Rationale |
| --- | --- |
| Delete legacy database data | It is explicitly considered disposable production data. |
| Retain physical attachments | Explicit product decision; files are few and must not be touched. |
| Use `410 Gone` | Clearly communicates permanent retirement to direct URLs and bookmarks. |
| Remove legacy code rather than archive it | It has no retained business value or maintenance path. |
| Keep Sidang Nol separate | Its current mechanism and database are sound. |

## Contract and data changes

| Change | Detail |
| --- | --- |
| `template_surat` | Drop `jenis_surat_id` and its foreign key; preserve template data and paths. |
| `notes` | Delete only `jenis_berkas = '1'` rows. |
| `berkas_persuratan` | Drop the table and its legacy data. |
| `jenis_surat` | Drop after Template Surat is independent. |
| Legacy HTTP routes | Replace with `410`; no successor API is introduced here. |

## Impacted components

### New files

| File | Purpose |
| --- | --- |
| `database/migrations/{timestamp}_decouple_template_surat_from_jenis_surat.php` | Remove the old Template Surat foreign-key dependency. |
| `database/migrations/{timestamp}_retire_legacy_persuratan_and_jenis_surat.php` | Delete legacy notes and drop retired tables without touching storage. |
| `tests/Feature/LegacyModuleRetirementTest.php` | Verify retirement URLs, Dashboard safety, and Template Surat behavior. |
| `docs/PRD/002_LEGACY_RETIREMENT_AND_TEMPLATE_SURAT_DECOUPLING.md` | This PRD. |

### Modified files

| File | Change |
| --- | --- |
| `routes/web.php` | Remove legacy handlers, add `410` fallbacks, and make Dashboard role-safe. |
| `app/Http/Controllers/TemplateSuratController.php` | Remove `JenisSurat` relation, query, validation, and search behavior. |
| `app/Models/TemplateSurat.php` | Remove `jenis_surat_id` and relation. |
| `app/Models/Note.php` | Remove the legacy Berkas Persuratan relation. |
| `app/Http/Controllers/DashboardController.php` | Remove legacy queries and redirect students to Sidang Nol. |
| `resources/js/pages/TemplateSurat/*.vue` | Remove legacy-type props, form field, and relation display. |
| `resources/js/pages/Dashboard/index.vue` | Remove Persuratan tab, metrics, filters, and charts. |
| `resources/js/components/AppSidebarMenu.ts` | Remove legacy module links. |
| `resources/js/pages/LandingPage/Index.vue` | Send logged-in students to Sidang Nol. |
| `database/seeders/DatabaseSeeder.php` | Remove retired seeder registrations. |
| `docs/README.md`, `docs/architecture.md`, `docs/data-model.md`, `docs/auth-authorization.md`, `docs/modules/template-surat.md` | Synchronize retained-scope documentation. |

### Removed files

| File or directory | Reason |
| --- | --- |
| `app/Http/Controllers/BerkasPersuratanController.php` | Retired controller. |
| `app/Http/Controllers/JenisSuratController.php` | Retired controller. |
| `app/Models/BerkasPersuratan.php`, `app/Models/JenisSurat.php` | Retired models. |
| `app/Policies/BerkasPersuratanPolicy.php` | Retired policy. |
| `app/Exports/BerkasPersuratanExport.php` | Retired export. |
| `resources/js/pages/BerkasPersuratan/`, `resources/js/pages/JenisSurat/` | Retired frontend modules. |
| `database/seeders/JenisSuratSeeder.php` | Retired master-data seeder. |

## Approved implementation tasks

### Task 1 — Decouple Template Surat

Remove all `jenis_surat` use from the Template Surat schema, model,
controller, and pages.

Acceptance criteria:

- Existing templates can be listed, edited, downloaded, and published.
- The form no longer asks for a legacy type.
- No template query uses `jenisSurat` or `jenis_surat_id`.

Dependencies: None.

Verification: `php artisan test --filter=LegacyModuleRetirementTest` and
`pnpm build`.

Risk and rollback: Verify templates before a drop; recover from the database
backup if necessary.

### Task 2 — Retire legacy data and code

Delete legacy notes, drop legacy tables, and remove the obsolete module files
and seeders. Do not touch storage.

Acceptance criteria:

- Legacy records and tables are absent.
- No active class references a removed legacy class or table.
- Every legacy private file remains unchanged.

Dependencies: Task 1.

Verification: `php artisan migrate:status` and
`php artisan test --filter=LegacyModuleRetirementTest`.

Risk and rollback: Destructive operation; database backup is mandatory and
Laravel migration rollback is not data recovery.

### Task 3 — Replace entry points and Dashboard dependencies

Expose `410` fallbacks, remove navigation, repair landing-page student routing,
and remove Dashboard dependencies on dropped data.

Acceptance criteria:

- Legacy paths return `410`.
- Sidebar and Dashboard contain no old workflow.
- Students reach Sidang Nol and non-students reach Dashboard.

Dependencies: Tasks 1 and 2.

Verification: `php artisan route:list`,
`php artisan test --filter=LegacyModuleRetirementTest`, and `pnpm build`.

### Task 4 — Synchronize documentation and regressions

Update retained-scope documentation and run the complete verification suite.

Acceptance criteria:

- No documentation says Template Surat depends on Jenis Surat.
- Documentation distinguishes retired legacy modules from future Letters.

Dependencies: Tasks 1–3.

Verification: `php artisan test`, `pnpm build`, and `git diff --check`.

## Requirement coverage

| Requirement | Task | Verification |
| --- | --- | --- |
| Decouple Template Surat | 1 | Feature test and manual template management/download flow |
| Delete legacy data and tables | 2 | Migration status and database inspection |
| Preserve physical attachments | 2 | No Storage mutation code and directory comparison |
| Return `410` for legacy URLs | 3 | Feature route assertions |
| Keep Sidang Nol operational | 3–4 | Existing Sidang Nol tests and manual regression |
| Remove Dashboard legacy dependencies | 3 | Dashboard check after migration |
| Update documentation | 4 | Documentation review and diff check |

## Risks and mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Irrecoverable deletion of database data | High | Verified backup and row-count precheck before production migration. |
| Template Surat unavailable after drop | High | Decouple and verify before dropping `jenis_surat`. |
| Dashboard queries a missing table | High | Remove server and Vue dependencies before destructive deployment. |
| Legacy files accidentally removed | High | No storage delete, move, or cleanup operation is in scope. |
| Stale bookmark ambiguity | Medium | Explicit `410 Gone` fallbacks cover legacy web and API paths. |

## Open questions

- None.

## Final verification

- [ ] Production database backup and row-count precheck completed (deployment prerequisite).
- [x] Template Surat create, edit, list, landing display, and download work.
- [x] Retired routes return `410 Gone`.
- [x] Dashboard has no Persuratan query.
- [x] Sidang Nol and retirement regressions pass.
- [ ] Full `php artisan test` passes — pre-existing stock authentication tests do not match the custom NIM-based authentication contract.
- [x] `pnpm build` passes.
- [x] `git diff --check` passes.
