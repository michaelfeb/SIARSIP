# Unified Letters Dashboard

**Status**: Draft
**Version**: 1.0
**Plan Status**: Approved
**Plan Version**: 1
**Mode**: full-stack
**Requirement Source**: User-provided dashboard reference image and approved implementation plan in chat.

---

## Overview

Replace the current Berkas Sidang Nol-only dashboard with one dashboard that
aggregates Berkas Sidang Nol and Cuti Akademik. It provides letter type, month,
and year filters; a program-study distribution; and summary counts for total,
in-process, archived, and rejected submissions.

## Background

The current dashboard queries only Berkas Sidang Nol and keeps aggregation
logic in its controller. Cuti Akademik is now a completed letter module with
its own workflow model, so staff need a common dashboard that follows the
approved visual reference without changing either workflow.

## User Story

- As a **staff user**, I want a combined view of current letter submissions,
  so that I can monitor approvals and outcomes across Berkas Sidang Nol and
  Cuti Akademik.

## Scope

- Aggregate Berkas Sidang Nol and Cuti Akademik submissions.
- Filter the dashboard by letter type, month, and year.
- Display total, in-process, archived, and rejected summary cards.
- Display filtered program-study distribution in a doughnut chart with a
  scrollable legend.
- Keep the existing student redirect to Berkas Sidang Nol.
- Move dashboard input validation and aggregation to a Form Request and a
  module service.
- Keep Dashboard-specific Vue components and TypeScript support files inside
  the Dashboard module folder.

## Out of Scope

- Adding any future Coming Soon letter modules as data sources.
- Changing Sidang Nol or Cuti Akademik workflow states, routes, records, or
  permissions.
- Database schema migrations or new API routes.
- Replacing the project UI stack or promoting Dashboard-only components to
  global shared components.

## Acceptance Criteria

- [ ] The dashboard presents only submitted Berkas Sidang Nol and Cuti
  Akademik records in one combined view.
- [ ] Users can filter by all or one letter type, all or one month, and all or
  one available year.
- [ ] The dashboard layout follows the supplied reference on desktop and
  stacks cleanly on small screens.
- [ ] The four summary cards and the program-study distribution update for
  every filter combination.
- [ ] A period with no matching records displays zero totals and an explicit
  empty distribution state.
- [ ] Student dashboard behavior remains a redirect to Berkas Sidang Nol.

## Functional Requirements

- Normalize the source workflows as follows:

  | Dashboard metric | Berkas Sidang Nol | Cuti Akademik |
  |---|---|---|
  | Total Surat | statuses `1`, `2`, `3` | `IN_PROGRESS`, `ARCHIVED`, `REJECTED` |
  | Dalam Proses | status `1` / Menunggu | `IN_PROGRESS` |
  | Diarsipkan | status `2` / Diterima | `ARCHIVED` |
  | Ditolak | status `3` | `REJECTED` |

- Exclude drafts from every metric and distribution.
- Filter Sidang Nol by `tanggal_dikirim` and Cuti Akademik by `submitted_at`.
- Show type, month, and year controls with an all-values default.
- Keep all dashboard data server-owned and refresh it through Inertia navigation.

## Non-Functional Requirements

- Reuse the existing Vue 3, Inertia, Tailwind, local UI-control, Lucide, and
  Chart.js stack; no framework migration is authorized.
- Aggregate through database-level counts and grouped queries per source rather
  than loading complete record sets into memory.
- Preserve existing route and authorization behavior.

## Edge Cases

| Scenario | Expected behavior |
|---|---|
| No matching records for active filters | All cards show `0` and the distribution presents an accessible empty state. |
| A record is still a draft | It is excluded from all dashboard results. |
| Sidang Nol record has status `2` | It is counted as Diarsipkan because its final response letter has been generated. |
| Student visits `/dashboard` | Existing redirect to Berkas Sidang Nol remains unchanged. |
| Invalid filter query | The request validation rejects the invalid value rather than producing incorrect totals. |

## Existing-System Findings

| Area | Evidence | Planning implication |
|---|---|---|
| Dashboard | `app/Http/Controllers/DashboardController.php`, `resources/js/pages/Dashboard/index.vue` | Replace controller-owned Sidang Nol query logic with a Dashboard request/service and reference-based UI. |
| Cuti workflow | `app/Services/CutiAkademikService.php`, `app/Models/Letter.php` | Use persisted lifecycle states and `submitted_at` as the aggregation source. |
| Sidang Nol workflow | `app/Http/Controllers/BerkasSidangNolController.php`, `app/Models/BerkasSidangNol.php` | Normalize numeric statuses and `tanggal_dikirim` within the Dashboard service. |
| UI stack | `package.json`, Dashboard page, `resources/js/components/ui/` | Reuse Vue 3.5, Inertia Vue 3, Tailwind 4, local controls, Lucide, and Chart.js. |

## Frontend Stack Profile

- Framework: Vue `3.5.44` with Inertia Vue `3.9.1` and TypeScript.
- Styling: Tailwind CSS `4.3.3` and project-owned local UI components.
- Charts: Chart.js `4.5.1` with vue-chartjs `5.3.4`.
- Icons: lucide-vue-next `1.0.0`.
- Data flow: server page props and Inertia GET navigation.
- Tests: no frontend test command is configured; PHP feature tests and a
  production Vite build are available.
- Migration authorized: No.

## Design and Architecture

### Ownership

`DashboardRequest` validates query input. `DashboardService` owns normalized,
combined aggregate queries. `DashboardController` performs only the existing
student redirect and Inertia response. `resources/js/pages/Dashboard/` owns
the Dashboard-specific layout, types, constants, and local visual components.

### Flow

```text
Dashboard filter change
  → Inertia GET /dashboard with query values
  → DashboardRequest validates and normalizes filters
  → DashboardService aggregates each source and normalizes statuses
  → DashboardController returns Dashboard page props
  → Filter card, distribution, and metric cards render the same response
```

### Decisions and Rationale

| Decision | Rationale |
|---|---|
| Use one Dashboard service instead of changing either letter service | The dashboard is the only consumer that combines the two distinct record models. |
| Treat Sidang Nol status `2` as archived | Its accepted decision generates the final response letter, matching the dashboard outcome. |
| Exclude drafts | Drafts have not entered the approval process and would distort operational reporting. |
| Keep components local to Dashboard | They have one current consumer and should not create premature shared abstractions. |

## Contract and Data Changes

The existing `GET /dashboard` Inertia page receives validated query filters:

```text
letter_type: all | sidang-nol | cuti-akademik
month: 1..12 | null
year: integer | null
```

It returns page props for normalized filters, available filter options, a
`summary` object (`total`, `inProgress`, `archived`, `rejected`), and
program-study distribution entries (`label`, `total`). No database schema,
external API, or route changes are made.

## Impacted Components

### New Files

| File | Purpose |
|---|---|
| `app/Http/Requests/DashboardRequest.php` | Validates and normalizes dashboard filters. |
| `app/Services/DashboardService.php` | Owns combined Sidang Nol and Cuti Akademik aggregation. |
| `resources/js/pages/Dashboard/components/DashboardFilters.vue` | Filter-card UI. |
| `resources/js/pages/Dashboard/components/DashboardDistribution.vue` | Doughnut chart, center total, legend, and empty state. |
| `resources/js/pages/Dashboard/components/DashboardSummaryCard.vue` | Local metric-card presentation. |
| `resources/js/pages/Dashboard/ts/dashboard.types.ts` | Dashboard page-prop and filter types. |
| `resources/js/pages/Dashboard/ts/dashboard.constants.ts` | Filter labels, month labels, and chart colors. |
| `docs/modules/dashboard.md` | Dashboard sources, status mapping, filters, and extension rules. |

### Modified Files

| File | Change |
|---|---|
| `app/Http/Controllers/DashboardController.php` | Delegate validated aggregation to `DashboardService`. |
| `resources/js/pages/Dashboard/index.vue` | Compose the new Dashboard layout. |
| `tests/Feature/DashboardTest.php` | Verify combined aggregation, filters, redirect, and empty results. |

## Approved Implementation Tasks

### Task 1 — Establish the dashboard query contract

Create the Dashboard request and service with grouped aggregate queries for
each source, then merge only the aggregate result.

Acceptance criteria:

- [ ] All filters default to all values.
- [ ] Both modules contribute to summary and distribution data.
- [ ] Drafts are excluded.
- [ ] The student redirect is unchanged.

Files:

- New: `app/Http/Requests/DashboardRequest.php`
- New: `app/Services/DashboardService.php`
- Modified: `app/Http/Controllers/DashboardController.php`

Dependencies: None.

Verification:

- Automated: `php artisan test tests/Feature/DashboardTest.php`
- Manual: Load `/dashboard` as a staff user and confirm default totals.

Risk and rollback: Query-only change; restore the previous controller response
if a regression is found.

### Task 2 — Add dashboard regression coverage

Extend the dashboard feature test with both record types across statuses,
programs, months, and years.

Acceptance criteria:

- [ ] Each normalized summary count is correct.
- [ ] Every filter applies to both sources.
- [ ] No-result filters produce zero totals and an empty distribution.

Files:

- Modified: `tests/Feature/DashboardTest.php`

Dependencies: Task 1.

Verification:

- Automated: `php artisan test tests/Feature/DashboardTest.php`
- Manual: Not required.

Risk and rollback: Tests only; no runtime rollback required.

### Task 3 — Implement the reference-based Dashboard UI

Build local Dashboard components for filters, doughnut distribution, and the
four status cards. Refresh data through Inertia when any filter changes.

Acceptance criteria:

- [ ] The desktop layout matches the supplied reference hierarchy.
- [ ] The layout stacks on small screens.
- [ ] Filter updates refresh every visual in the dashboard.
- [ ] Empty distribution state is readable and accessible.

Files:

- New: `resources/js/pages/Dashboard/components/DashboardFilters.vue`
- New: `resources/js/pages/Dashboard/components/DashboardDistribution.vue`
- New: `resources/js/pages/Dashboard/components/DashboardSummaryCard.vue`
- New: `resources/js/pages/Dashboard/ts/dashboard.types.ts`
- New: `resources/js/pages/Dashboard/ts/dashboard.constants.ts`
- Modified: `resources/js/pages/Dashboard/index.vue`

Dependencies: Task 1.

Verification:

- Automated: `npx prettier --check resources/js/pages/Dashboard` and `npm run build`
- Manual: Check filter changes, zero state, keyboard access, responsive layout,
  and browser console/network activity at `/dashboard`.

Risk and rollback: Local presentation change; restore the prior page component
if visual regressions occur.

### Task 4 — Document the dashboard contract

Document sources, status normalization, filters, and how a future letter type
is intentionally added.

Acceptance criteria:

- [ ] The document names the two current sources and their status mapping.
- [ ] It explains the controlled extension point for future letter modules.

Files:

- New: `docs/modules/dashboard.md`

Dependencies: Tasks 1–3.

Verification:

- Automated: Not applicable.
- Manual: Compare the document with the implemented request/service contract.

Risk and rollback: Documentation-only; amend the document if implementation
changes within approved scope.

## Requirement Coverage

| Requirement or edge case | Task | Verification |
|---|---|---|
| Combined Sidang Nol and Cuti Akademik view | Tasks 1–3 | Dashboard feature test and browser check |
| Letter type, month, and year filters | Tasks 1–3 | Dashboard feature test and browser check |
| Reference-based UI | Task 3 | Production build and responsive browser check |
| Normalized archived status for Sidang Nol accepted records | Tasks 1–2 | Dashboard feature test |
| Empty result handling | Tasks 2–3 | Dashboard feature test and manual zero-state check |
| Student redirect | Tasks 1–2 | Dashboard feature test |
| Future-module extension documentation | Task 4 | Manual documentation review |

## Risks and Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Different status models | Medium | Normalize only inside `DashboardService`; leave workflows unchanged. |
| Future data growth | Medium | Use database-level grouped aggregates per source. |
| Future letter status differences | Low | Document the explicit mapping and extension point. |

## Open Questions

- None

## Final Verification

- [ ] `php artisan test tests/Feature/DashboardTest.php`
- [ ] Relevant Cuti Akademik and Sidang Nol regression tests.
- [ ] `npx prettier --check resources/js/pages/Dashboard`
- [ ] `npm run build`
- [ ] Manual staff browser test for filters, responsive layout, keyboard focus,
  empty state, and browser console/network errors.
- [ ] Final diff review confirming no UI-stack mixing and no unintended changes
  to existing workflow routes or permissions.

## API Reference

_Not applicable. The existing authenticated Inertia `GET /dashboard` route is
extended; no separate public API is introduced._

## Version History

| Version | Date | Author | Notes |
|---|---|---|---|
| 1.0 | 2026-10-29 | Full-stack Team | Initial PRD from approved implementation plan |
