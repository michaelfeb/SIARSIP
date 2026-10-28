# Template Surat Alignment

**Status**: Draft
**Version**: 1.0
**Plan Status**: Approved
**Plan Version**: 1
**Mode**: full-stack
**Requirement Source**: Approved chat plan; `docs/frontend-development-base.md` and `docs/backend-development-base.md`

---

## Overview

Align Template Surat with the SIARSIP revamp conventions. Its admin and student
lists will use typed Vue/Inertia page data and a local table coordinator; its
form and detail page will use feature-local components and shared controls. The
Laravel boundary will use a Form Request and one module service without changing
the database schema, public download URL, stored document location, or role
middleware.

## Background

Template Surat currently owns direct Axios data fetching, direct SweetAlert
calls, untyped page props, and table state in multiple pages. Its controller
also contains request validation, queries, and document-storage workflows. The
revamp standards designate Berkas Sidang Nol as the Vue/Inertia boundary and a
Route → Controller → Form Request → Service flow for retained Laravel modules.

## User Story

- As a staff member, I can search, manage, view, download, and remove letter
  templates through a consistent responsive interface.
- As a student, I can browse and download published templates without seeing
  inactive records.
- As a maintainer, I can extend the module through typed feature boundaries and
  a predictable Request/Service backend flow.

## Scope

- Refactor management and student indexes to use Inertia paginator/filter props.
- Add one feature-local `DataTable.vue`, typed TypeScript support files, and
  module-only details and document-upload components.
- Rebuild Form and Show with shared controls and shared alert helpers.
- Add `TemplateSuratRequest` and `TemplateSuratService` and make the controller
  a thin HTTP boundary.
- Retain template fields, storage disk/path, public download behavior, and
  existing role middleware.

## Out of Scope

- Database migrations or template field changes.
- Landing-page redesign.
- A new frontend test runner.
- Changing the public `/template-surat/download/{id}` URL.

## Acceptance Criteria

- [ ] Admin list has debounced search, pagination, loading, empty, and responsive overflow states.
- [ ] Student list uses its published-only endpoint and cannot receive inactive templates through its UI query.
- [ ] Template Surat pages have no direct Axios requests, `vue3-easy-data-table` ownership, raw `any` props, or direct `Swal.fire` calls.
- [ ] Create/update shows shared feedback, validates document requirements, and preserves an unchanged document on edit.
- [ ] Show presents a readable definition list and downloads through the official endpoint.
- [ ] Validation, query, and storage workflows live in the Form Request and Service.

## Functional Requirements

- Search filters by template name, resets to page one, and preserves query state in the URL.
- Management users can create, view, edit, delete, and download a template; destructive deletion requires confirmation.
- Student listings contain only active templates and offer detail and download actions.
- Create requires a DOC/DOCX document up to 2 MB. Edit may retain the current document or replace it.
- A successful replacement removes the superseded public-disk document only after the record update succeeds; deletion removes the associated document.
- Existing public JSON responses remain available when explicitly requested.

## Non-Functional Requirements

- Preserve Vue 3, Inertia, Tailwind, local shadcn-style primitives, FilePond,
  Lucide, and shared SweetAlert styling; no UI-stack migration or new dependency.
- Use labels, keyboard-reachable action buttons, visible focus states, semantic
  status text, and responsive table overflow.
- Preserve existing routes, storage namespace, and middleware behavior.

## Edge Cases

| Scenario | Expected behavior |
|---|---|
| No records match a search | The shared table shows its empty state and no invalid pagination controls. |
| Search changes off the first page | The request is reissued for page one after the debounce. |
| Student visits the student list | Only `status = 1` records are returned. |
| Create without a file or with an invalid file | Server validation returns the field error; no record is stored. |
| Edit without a replacement file | The current `dokumen_path` remains unchanged. |
| Edit with a replacement file | The new file is retained and the previous stored file is removed after success. |
| Description or publish date is null in page data | The UI renders an em dash rather than an empty/broken value. |
| A delete is cancelled | No mutation request is sent. |

## Existing-System Findings

| Area | Evidence | Planning implication |
|---|---|---|
| Module pages | `resources/js/pages/TemplateSurat/` | Add the documented local table, components, and `ts/` boundary. |
| Shared table | `resources/js/components/data-table/DataTable.vue` | Use it as the visual and pagination base. |
| Shared alerts | `resources/js/components/alerts/SwalAlert.ts` | Reuse confirmation, success, and error helpers. |
| Similar module | `resources/js/pages/CutiAkademik/` | Follow its page/table Inertia ownership split. |
| Backend | `app/Http/Controllers/TemplateSuratController.php` | Move inline concerns into a Request and Service. |

## Frontend Stack Profile

Vue 3.5 with TypeScript and Inertia Vue 3; Tailwind CSS 4; owned shadcn-style
controls under `resources/js/components/ui`; Reka UI primitives; shared table
under `resources/js/components/data-table`; FilePond documents; SweetAlert2 via
`SwalAlert.ts`; Lucide Vue icons. Route navigation and form state use
`@inertiajs/vue3`. There is no configured frontend unit or browser-test runner.
Migration authorized: No.

## Design and Architecture

### Ownership

`Index.vue` and `IndexMahasiswa.vue` own navigation and Inertia partial reloads.
The local `DataTable.vue` owns search/filter state, headers, table slots, and
emitted intents. `Form.vue` owns `useForm`; `Show.vue` composes read-only
details. The Laravel controller chooses Inertia/JSON/redirect/download responses;
the Request validates input and the Service owns queries and file workflows.

### Flow

User query → local table emits query → page performs an Inertia partial reload
→ controller validates filters → service returns paginator → page props update.
Form submission → Request validation → Service stores or updates the record and
document → redirect → shared alert feedback. Delete → shared confirmation →
page mutation → Service removes model and public-disk document.

### Decisions and Rationale

| Decision | Rationale |
|---|---|
| One local table with management/student variants | Avoids duplicated filtering, pagination, cells, and responsive behavior. |
| URL-driven Inertia list state | Removes duplicate Axios state and supports refresh/back navigation. |
| Page-owned mutations | Keeps routes, confirmations, and navigation out of the shared visual wrapper. |
| Separate `store` and `update` controller methods | Makes the HTTP boundary readable while keeping the existing save URIs. |
| Replace storage safely | Prevents removal of the old document until replacement persistence has succeeded. |

## Contract and Data Changes

No schema changes. Index responses additionally provide typed `filters`
(`search`, `per_page`). The save URI remains `/template-surat/save/{id?}` but
uses canonical `template-surat.store` and `template-surat.update` names.

## Impacted Components

### New Files

| File | Purpose |
|---|---|
| `resources/js/pages/TemplateSurat/DataTable.vue` | Feature table filters, cells, and emitted actions. |
| `resources/js/pages/TemplateSurat/components/TemplateSuratDetails.vue` | Read-only template details. |
| `resources/js/pages/TemplateSurat/components/TemplateSuratDocumentUpload.vue` | DOC/DOCX FilePond upload. |
| `resources/js/pages/TemplateSurat/ts/templateSurat.types.ts` | Record, paginator, filter, actor, and query contracts. |
| `resources/js/pages/TemplateSurat/ts/templateSurat.constants.ts` | Status and document constraints. |
| `resources/js/pages/TemplateSurat/ts/templateSurat.formatters.ts` | Date display helper. |
| `resources/js/pages/TemplateSurat/ts/templateSurat.capabilities.ts` | Presentation-only action visibility. |
| `app/Http/Requests/TemplateSuratRequest.php` | Filter and mutation validation. |
| `app/Services/TemplateSuratService.php` | Queries and storage lifecycle. |
| `tests/Feature/TemplateSuratTest.php` | Filter, storage, save/update, and download coverage. |

### Modified Files

| File | Change |
|---|---|
| `resources/js/pages/TemplateSurat/Index.vue` | Inertia query/navigation and management actions. |
| `resources/js/pages/TemplateSurat/IndexMahasiswa.vue` | Published-list page contract. |
| `resources/js/pages/TemplateSurat/Form.vue` | Typed form, shared feedback, and document component. |
| `resources/js/pages/TemplateSurat/Show.vue` | Detail composition and official download action. |
| `app/Http/Controllers/TemplateSuratController.php` | Thin controller. |
| `app/Models/TemplateSurat.php` | Boolean/date casts. |
| `routes/web.php` | Dedicated store/update routes with unchanged URI/middleware behavior. |
| `docs/modules/template-surat.md` | Final module contract. |
| `tests/Feature/LegacyModuleRetirementTest.php` | Canonical update-route assertion. |

## Approved Implementation Tasks

1. Establish typed frontend support files and the local shared-table wrapper.
2. Refactor management and student index pages to URL-driven Inertia queries.
3. Rebuild Form and Show around module-local upload/detail components.
4. Align the Laravel Request, Service, controller, model, routes, and tests.
5. Update documentation and run formatting, build, focused feature tests, and manual UI checks.

## Risks and Mitigations

| Risk | Mitigation |
|---|---|
| Orphaned replacement file | Delete the old file only after successful persistence; remove a new file on failure. |
| Student sees unpublished data | Service applies a published-only query and tests it. |
| Route refactor breaks a caller | Preserve URI/middleware and update route-name callers with feature coverage. |
| List implementations diverge | Use exactly one local Template Surat table over the shared table shell. |

## Open Questions

- None

## Final Verification

- `pnpm run format:check`
- `pnpm exec eslint resources/js/pages/TemplateSurat --max-warnings=0`
- `pnpm run build`
- `php artisan test tests/Feature/TemplateSuratTest.php tests/Feature/LegacyModuleRetirementTest.php`
- Manual management/student, responsive, keyboard, focus, validation, and browser-console/network checks.

## API Reference

_Not applicable — this module continues to use its existing Inertia and JSON endpoint boundary._

## Version History

| Version | Date | Author | Notes |
|---|---|---|---|
| 1.0 | 2026-10-27 | Full-stack Team | Initial PRD from the approved implementation plan. |
