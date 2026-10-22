# PRD 001 — Sidang Nol Revamp

**Status:** Done<br>
**Created:** 22 October 2026<br>
**Completed:** 22 October 2026
**Area:** Berkas Sidang Nol index table  
**Mode:** Full stack — scoped Laravel query support and Vue/Inertia UI revamp

## 1. Background

The Berkas Sidang Nol listing works, but its current index page combines page layout, Inertia query state, search UI, table markup, cell rendering, and row actions in one file. The application also needs a reusable visual table base for later module revamps.

This work uses Berkas Sidang Nol as the first reference implementation. It introduces only a small shared table shell and keeps the feature-specific table experience in one local component, so the structure stays easy to review and split later only if needed.

## 2. User story

As an authorised SIARSIP user, I want to search and filter Berkas Sidang Nol records in a clearer, consistent table interface so that I can find and act on submissions efficiently.

As a developer, I want a shared table visual base and a feature-local table component so that future modules can reuse the UI without inheriting Sidang Nol business rules.

## 3. Goals

- Revamp the Berkas Sidang Nol index to follow the approved table UI direction: clear heading/actions, a `Cari & Filter` area, a rounded table surface, status badges, and icon actions.
- Add a reusable application table wrapper with the exact initial structure below.
- Keep search and the Berkas Sidang Nol status filter in `resources/js/pages/BerkasSidangNol/DataTable.vue`.
- Make the status filter functional through validated URL query parameters and the agreed controller → Form Request → service approach.
- Preserve current permissions, pagination, actions, export flow, and Inertia behavior.

## 4. Approved structure

```text
resources/js/components/data-table/
├── DataTable.vue
└── DataTable.types.ts

resources/js/pages/BerkasSidangNol/
├── DataTable.vue
├── Index.vue
└── ts/
```

No additional table components are part of this PRD. If the local Sidang Nol table becomes difficult to maintain after use, its responsibilities may be split in a later approved change.

## 5. Scope

### Included

- A shared `DataTable.vue` wrapper around the installed `vue3-easy-data-table` package.
- Shared table contracts in `DataTable.types.ts`.
- A local `BerkasSidangNol/DataTable.vue` containing:
  - search input and debounce handling;
  - status-filter control;
  - pagination interaction;
  - Sidang Nol headers and cell rendering;
  - status badges;
  - permission-aware `rounded-lg` table action buttons.
- An Inertia query flow for `search`, `status`, `page`, and `per_page`.
- A minimal index-query refactor into `IndexSidangNolRequest` and `SidangNolService::datatable()`.
- Backend test coverage for visibility and status filtering.

### Excluded

- **Stage** column, stage filter, stage data, and stage-related backend logic.
- New database tables, columns, or migrations.
- Replacing `vue3-easy-data-table`.
- Migrating any other module to the shared table base.
- Refactoring non-index Berkas Sidang Nol controller methods.
- Adding interactive column sorting. The current backend has fixed ordering, so the UI must not imply arbitrary sorting is available.

## 6. Functional requirements

1. `Index.vue` remains the Inertia page entry and owns the page heading, breadcrumbs, top-level Export/Tambah actions, export modal, server request, and mutation handlers.
2. `BerkasSidangNol/DataTable.vue` owns the list UI and emits event intents to `Index.vue`.
3. Search filters existing server-side results by the current supported fields: student name, NIM, letter number, and program studi.
4. Status filtering supports the existing status domain:
   - `0` Draft
   - `1` Menunggu
   - `2` Diterima
   - `3` Ditolak
5. An empty status means all records already visible to the authenticated user; it never bypasses existing role-based visibility rules.
6. Student users only see their own records. Staff visibility remains limited to records with status `>= 1`.
7. The staff status-filter control must not offer Draft, because staff cannot see draft records under the current visibility rule.
8. Existing row action visibility continues to use `sidangNolCapabilities()` as frontend presentation logic; Laravel remains the authorization authority.
9. Existing actions—detail, review, submit, reset, edit, delete, and reply-letter download—continue to work where permitted.
10. Template event handlers use the documented `on...` convention, such as `onSearchChange()`, `onStatusFilterChange()`, `onSubmit(berkas)`, and `onDelete(berkas)`.

## 7. Non-functional requirements

- Preserve Vue `3.5.44`, Inertia Vue `3.9.1`, TypeScript `5.9.3`, Tailwind CSS `4.3.3`, and `vue3-easy-data-table` `1.5.47`.
- Reuse existing `Button`, `Input`, modal, Lucide icon, and SweetAlert2 conventions.
- Keep the table usable on narrow screens through horizontal overflow rather than hiding data.
- Preserve URL query state across pagination and partial Inertia reloads.
- Do not add a second request/state library or a new UI framework.

## 8. UI and ownership design

```text
Index.vue
├── App layout, heading, Export/Tambah actions, export modal
├── onDataTableQueryChange(query)
├── onDetail(), onEdit(), onDelete(), onSubmit(), and other mutation handlers
└── BerkasSidangNol/DataTable.vue
    ├── Cari & Filter controls: search + status
    ├── module columns, badges, and action controls
    ├── local on... UI handlers
    └── shared components/data-table/DataTable.vue
        ├── table frame and responsive overflow
        ├── common table/header/loading/empty/pagination styling
        └── forwarded cell slots
```

### Component contracts

`resources/js/components/data-table/DataTable.vue` is presentation-only. It receives generic table headers, rows, loading state, total rows, and server options. It forwards named table slots and emits changed server options. It must contain no route name, feature terminology, status meaning, or permission condition.

`resources/js/pages/BerkasSidangNol/DataTable.vue` receives the paginated Berkas Sidang Nol data, authenticated actor, current query state, and loading state. It emits:

```text
query-change({ page, perPage, search, status })
view(id)
edit(id)
delete(berkas)
submit(berkas)
reset(berkas)
review(id)
download-reply(berkas)
```

The local component may render its own headers, badges, and actions because they are feature-specific.

### Backend query contract

The index accepts these query parameters:

| Parameter | Type | Behavior |
| --- | --- | --- |
| `search` | nullable string | Searches the existing supported fields. |
| `status` | nullable integer in `0,1,2,3` | Restricts only the records already visible to the current user. |
| `page` | positive integer | Laravel pagination page. |
| `per_page` | approved positive page size | Laravel pagination size. |

`IndexSidangNolRequest` validates and normalizes this query. `SidangNolService::datatable(User $user, array $filters)` owns the complete Eloquent query: relationship loading, actor visibility, search, status condition, existing default order, and pagination with query-string preservation.

## 9. Files

### New files

| File | Purpose |
| --- | --- |
| `app/Http/Requests/SidangNol/IndexSidangNolRequest.php` | Validate and expose index query filters. |
| `app/Services/SidangNolService.php` | Own the paginated Sidang Nol index query in `datatable()`. |
| `resources/js/components/data-table/DataTable.vue` | Shared visual table wrapper. |
| `resources/js/components/data-table/DataTable.types.ts` | Shared generic table type contracts. |
| `resources/js/pages/BerkasSidangNol/DataTable.vue` | Local filters, list columns, cells, badges, and actions. |
| `tests/Feature/BerkasSidangNolIndexTest.php` | Index visibility and status-filter coverage. |

### Modified files

| File | Change |
| --- | --- |
| `app/Http/Controllers/BerkasSidangNolController.php` | Change only `index()` to delegate to the Request and service. |
| `resources/js/pages/BerkasSidangNol/Index.vue` | Become the page coordinator and compose the local table. |
| `resources/js/pages/BerkasSidangNol/ts/types.ts` | Add the local typed table query/event shape. |

## 10. Implementation tasks

### Task 1 — Build the shared visual table base

Create the two shared files under `resources/js/components/data-table/`. Wrap the existing data-table library, standardize its visual surface and responsive overflow, and forward named table slots.

Acceptance criteria:

- Generic headers, rows, loading, total count, and server-options state can be passed in.
- Existing `#header-*` and `#item-*` slot patterns remain available.
- No Sidang Nol language or business logic is included.

Verification:

- `pnpm exec eslint resources/js/components/data-table/DataTable.vue`
- Manual render through the Sidang Nol table.

### Task 2 — Add the validated Sidang Nol index query

Create `IndexSidangNolRequest` and `SidangNolService`. Change only the controller’s `index()` method to delegate query validation and data retrieval.

Acceptance criteria:

- Existing search, actor visibility, default order, pagination, and Inertia/JSON response behavior are preserved.
- `status` is validated and filters within actor-visible records.
- An invalid status fails validation rather than changing the query.

Verification:

- `php artisan test tests/Feature/BerkasSidangNolIndexTest.php`
- Manual requests with empty, valid, and invalid `status` query values.

### Task 3 — Build the local Sidang Nol table

Create `resources/js/pages/BerkasSidangNol/DataTable.vue`. Move current search, table, pagination, cell slots, status presentation, and action-button markup from the index page into it. Add the functional status filter beside search. Do not add Stage.

Acceptance criteria:

- Search and status changes return to page one and issue a debounced/controlled query-change event.
- The table preserves all permitted current actions.
- Status options reflect actor visibility: staff do not receive a Draft option.
- The UI follows the supplied reference’s layout without copying unrelated Cuti Akademik labels or data.

Verification:

- `pnpm exec eslint resources/js/pages/BerkasSidangNol/DataTable.vue`
- Manual desktop and narrow-screen inspection.

### Task 4 — Simplify the page coordinator and verify end-to-end behavior

Modify `Index.vue` to consume local table events, issue Inertia partial reloads, and retain the page-level modal/action logic.

Acceptance criteria:

- `Index.vue` contains no feature table markup or search/filter controls.
- Search, status filter, pagination, Export, Tambah, and all allowed row actions work after a partial reload.
- URL query values persist for page, rows per page, search, and status.

Verification:

- `pnpm exec eslint resources/js/pages/BerkasSidangNol/Index.vue`
- `pnpm build`
- Browser check as student and staff, including an empty-result state.

## 11. Edge cases

| Scenario | Expected behavior |
| --- | --- |
| Search returns no records | Show the shared empty-table state; preserve entered search and status values. |
| A user selects a status then changes search | Reset to page one and preserve the status filter. |
| A staff user attempts `?status=0` manually | Return no results; the visibility query remains authoritative. |
| An invalid `status` is supplied | Request validation rejects it. |
| An action changes/deletes the last record on a page | Reload the list through the existing page flow; pagination must not leave the user on an invalid empty page. |
| Narrow viewport | Table scrolls horizontally; controls stack without losing access to actions. |

## 12. Risks and mitigations

| Risk | Mitigation |
| --- | --- |
| Shared table becomes coupled to one module | Keep all Sidang Nol labels, columns, filters, and actions in the local table. |
| Status filter bypasses role visibility | Apply status after the actor visibility condition in `SidangNolService::datatable()`. |
| UI suggests unsupported sorting | Do not enable interactive sort controls in this scope. |
| Existing action permissions regress during extraction | Preserve `sidangNolCapabilities()` calls and verify every relevant status/role combination. |

## 13. Completion criteria

- [x] The shared table base exists only as `DataTable.vue` and `DataTable.types.ts`.
- [x] Berkas Sidang Nol has one local `DataTable.vue` containing its search, status filter, and table UI.
- [x] Stage does not appear in the UI, types, URL query, request validation, service query, or tests.
- [x] Search and status filtering work with the existing role visibility rules.
- [x] Existing page actions and export behavior remain intact.
- [x] Backend feature test, frontend lint checks, and production build pass.
- [x] Browser verification confirms desktop and narrow-screen usability.

## 14. Completion record

The Berkas Sidang Nol module is the completed reference implementation for the application revamp.

Delivered:

- Shared and module-local data-table structure, including responsive horizontal overflow, compact index column sizing, search, status filter, pagination, export, and permission-aware rounded action buttons.
- Validated index query flow through `IndexSidangNolRequest` and `SidangNolService::datatable()`.
- Revamped `Form.vue`, with shared-select usage and a responsive two-column document-upload layout.
- Revamped `Show.vue`, with clean request details and direct, toolbar-free document previews in a two-column document layout.
- Revamped `Ajuan.vue`, with separate request-detail and decision sections, signatory selection, decision inputs, direct document previews, and the existing cancel/reject/accept flows.
- Responsive behavior and the agreed muted-orange primary presentation across the module.

Verification completed on 22 October 2026:

- `php artisan test tests/Feature/BerkasSidangNolIndexTest.php` — passed: 5 tests, 17 assertions.
- Targeted frontend ESLint checks — passed.
- `pnpm build` — passed.
- Desktop and narrow-screen UI review — completed during implementation.

## 15. Follow-up opportunities

After this PRD is implemented and reviewed, migrate another list module to the shared base. Only then decide whether a further local slice is justified; do not add more table components pre-emptively.
