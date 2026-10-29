# Carousel Alignment

**Status**: Draft
**Version**: 1.0
**Plan Status**: Approved
**Plan Version**: 1
**Mode**: full-stack
**Requirement Source**: Approved chat plan; `docs/backend-development-base.md` and `docs/frontend-development-base.md`
**Created**: 29 October 2026

---

## Overview

Align Carousel with the SIARSIP Laravel Request/Service and Vue/Inertia module
conventions without changing its purpose: authorized staff manage announcement
images and the public landing page shows active images by publish date.

## Background

Carousel currently owns query, validation, storage, and mutation workflow in
its controller. Its index uses direct Axios, direct SweetAlert calls, and
page-owned table state. Deleting a Carousel row also leaves its public image
file behind. The retained-module conventions require a thin controller,
Form Request, module Service, local table coordinator, typed module support,
and shared alerts.

## User Story

- As an authorized staff member, I can search, create, edit, activate,
  deactivate, and delete announcement images through a consistent interface.
- As a landing-page visitor, I see only active carousel images in publication
  order.
- As a maintainer, I can extend Carousel through predictable Request, Service,
  and typed frontend boundaries.

## Scope

- Add `CarouselRequest` and `CarouselService`.
- Refactor the management list to Inertia paginator/filter props and a local
  `DataTable.vue` over the shared table shell.
- Refactor the form to typed `useForm`, a module image-upload component, and
  shared alerts.
- Split the combined save action into canonical `store` and `update` actions
  while retaining the existing management URI shape and middleware.
- Safely replace and delete public-disk image files.
- Move the active landing-page Carousel query into the Carousel service.
- Add feature tests and update module documentation.

## Out of Scope

- Database schema or Carousel-field changes.
- Retroactive scanning or removal of pre-existing orphaned image files.
- Landing-page visual redesign.
- New frontend dependencies or frontend test tooling.
- Changing `GET /carousel-image/{filename}`.

## Acceptance Criteria

- [ ] The list has debounced, URL-driven search; shared pagination; loading and
  empty states; and responsive overflow.
- [ ] Carousel pages contain no direct Axios calls, direct `Swal.fire` calls,
  page-owned EasyDataTable, or raw `any` props.
- [ ] Create and edit use typed Inertia forms, shared feedback, image preview,
  and server validation errors.
- [ ] Editing without a file preserves its image; a successful replacement
  removes the superseded image only after persistence.
- [ ] Toggle and delete actions use the service; deletion requires confirmation
  and removes the owned image.
- [ ] Roles 6 and 8 remain the only permitted management actors in both route
  middleware and mutating service methods.
- [ ] The landing page still receives only active Carousel records ordered by
  descending publication date.

## Functional Requirements

- `datatable()` validates and applies `search` and `per_page` filters.
- `create()`, `update()`, `toggleStatus()`, and `delete()` use explicit
  domain-oriented signatures; public combined `save()` is not used.
- The existing `/carousel/save` paths remain valid but receive separate
  `carousel.store` and `carousel.update` route names and controller actions.
- The public image endpoint returns the stored image or a 404 response.
- The local table emits query, edit, toggle, and delete intents; Index owns
  routing, confirmations, and mutations.

## Non-Functional Requirements

- Preserve Vue 3.5, TypeScript, Inertia, Tailwind 4, local shadcn-style
  primitives, FilePond, Lucide, and the shared alert/table utilities.
- Preserve the `public` disk and `carousel/` storage namespace.
- Keep controls keyboard reachable, labelled, and responsive. No UI-stack
  migration is authorized.

## Edge Cases

| Scenario | Expected behavior |
| --- | --- |
| Search has no matches | Shared table empty state is shown without invalid pagination. |
| Search changes off page one | Query returns to page one after debounce. |
| Create without an image or with an invalid image | Validation returns a field error and no record is stored. |
| Edit without a replacement image | Existing image path remains unchanged. |
| Edit with a replacement image | New image is stored and old image is removed after persistence. |
| Delete is cancelled | No request is sent. |
| Public image is missing | Public endpoint returns 404. |
| Inactive Carousel record | It remains manageable but is omitted from landing-page data. |

## Existing-System Findings

| Area | Evidence | Planning implication |
| --- | --- | --- |
| Controller | `app/Http/Controllers/CarouselController.php` | Move validation, queries, and storage workflow to Request/Service. |
| Routes | `routes/web.php` | Preserve paths and middleware while splitting the combined save action. |
| Pages | `resources/js/pages/Carousel/` | Add local table, module components, and typed support boundaries. |
| Shared UI | `resources/js/components/data-table/DataTable.vue`, `components/alerts/SwalAlert.ts` | Reuse table shell and alerts. |
| Landing page | `app/Http/Controllers/LandingPageController.php` | Delegate active-carousel lookup to the service. |
| Tests | No Carousel feature test exists. | Add authorization, validation, storage, mutation, and landing coverage. |

## Frontend Stack Profile

Vue 3.5 with TypeScript and Inertia Vue 3; Tailwind CSS 4; local
shadcn-style controls under `resources/js/components/ui`; Reka UI primitives;
shared `DataTable.vue`; FilePond; Lucide; and SweetAlert2 through
`components/alerts/SwalAlert.ts`. No frontend unit or browser-test runner is
configured. Migration authorized: No.

## Design and Architecture

### Ownership

- `Index.vue` owns navigation, Inertia partial reloads, confirmations, and
  mutations.
- Local `DataTable.vue` owns query controls, headers, cells, and emitted row
  intents.
- `Form.vue` owns typed `useForm` state; `CarouselImageUpload.vue` owns only
  FilePond image presentation and emitted files.
- `CarouselRequest` owns validated filters and mutation payloads.
- `CarouselService` owns Carousel queries, image lifecycle, state changes,
  landing records, and public image response.
- Controllers select only HTTP responses.

### Flow

```text
DataTable query → Index Inertia reload → CarouselRequest →
CarouselService::datatable() → paginator and filters props

Form submit → CarouselRequest → CarouselService::create()/update() →
redirect → shared alert

Delete confirmation → Index router.delete() → CarouselService::delete() →
record and owned image removal
```

### Decisions and Rationale

| Decision | Rationale |
| --- | --- |
| Keep `/carousel/save/{id?}` paths | Avoid a live URL migration while making action names explicit. |
| Use `store` and `update` route names/actions | Enforce the documented controller and service contract. |
| Preserve read-only publish date input | Alignment does not alter existing publication policy. |
| Use service-level role checks for mutations | Middleware is the first guard; service authorization is the required second line. |
| Do not clean historical orphan files | Safe cleanup applies only to records handled after this change. |

## Contract and Data Changes

No schema changes. Index props add validated `filters` beside the paginator.
`POST /carousel/save` becomes `carousel.store`, while
`PUT /carousel/save/{carousel}` becomes `carousel.update`; their URI paths,
roles, fields, storage disk, and JSON paginator behavior remain compatible.

## Impacted Components

### New Files

| File | Purpose |
| --- | --- |
| `app/Http/Requests/CarouselRequest.php` | Filter and form validation. |
| `app/Services/CarouselService.php` | Queries, service authorization, image lifecycle, and public image response. |
| `resources/js/pages/Carousel/DataTable.vue` | Local table filters, cells, and intents. |
| `resources/js/pages/Carousel/components/CarouselImageUpload.vue` | FilePond image input. |
| `resources/js/pages/Carousel/ts/carousel.types.ts` | Typed record, paginator, filter, actor, query, and form contracts. |
| `resources/js/pages/Carousel/ts/carousel.constants.ts` | Image and status display constants. |
| `resources/js/pages/Carousel/ts/carousel.capabilities.ts` | Presentation-only action visibility. |
| `tests/Feature/CarouselManagementTest.php` | End-to-end module coverage. |

### Modified Files

| File | Change |
| --- | --- |
| `app/Http/Controllers/CarouselController.php` | Thin controller and typed model binding. |
| `app/Http/Controllers/LandingPageController.php` | Service-owned active Carousel lookup. |
| `app/Models/Carousel.php` | Boolean status cast. |
| `routes/web.php` | Split store/update routes while preserving paths and guards. |
| `resources/js/pages/Carousel/Index.vue` | Inertia page orchestration and alerts. |
| `resources/js/pages/Carousel/Form.vue` | Typed form and image-uploader composition. |
| `docs/modules/carousel.md` | Implemented module contract. |
| `docs/README.md` | PRD link. |

## Approved Implementation Tasks

### Task 1 — Backend Request, Service, and routes

Create the Request and Service, move Carousel query/storage workflow out of the
controller, split save actions, preserve the public image endpoint, and
delegate the landing query to the service.

Acceptance criteria:

- [ ] Canonical service methods have explicit typed signatures.
- [ ] Mutations require roles 6 or 8 at the service boundary.
- [ ] Image replacement and deletion safely clean current owned files.
- [ ] Missing public images return 404.

Files: New `CarouselRequest.php`, `CarouselService.php`; modified Carousel and
Landing Page controllers, Carousel model, and routes.

Verification: `php artisan test tests/Feature/CarouselManagementTest.php` and
`php artisan route:list --name=carousel`.

### Task 2 — Typed Carousel frontend

Build the documented local component and `ts/` boundaries. Refactor Index and
Form to use Inertia, shared table/alerts/controls, typed props, and FilePond
image preview.

Acceptance criteria:

- [ ] The local table owns filters and emits intents only.
- [ ] Index owns route navigation, confirmation, and mutations.
- [ ] Form renders validation errors and image requirements accessibly.
- [ ] No direct Axios, raw EasyDataTable, direct SweetAlert, or raw `any`
  remains in Carousel pages.

Files: New Carousel DataTable, image upload component, and `ts/` files;
modified Carousel Index and Form.

Verification: changed-file Prettier/ESLint checks and manual responsive,
keyboard, empty-state, and mutation-flow checks.

### Task 3 — Regression tests and documentation

Add feature tests for authorization, validation, storage lifecycle, status,
public image, and landing filtering; update module documentation.

Acceptance criteria:

- [ ] Tests cover creation, retained-image edit, replacement, toggle, deletion,
  role restriction, and active-only landing records.
- [ ] Module documentation records safe image deletion and final routes.

Files: New Carousel feature test; modified Carousel module documentation and
documentation index.

Verification: targeted feature test, build, route-list, and final diff checks.

## Requirement Coverage

| Requirement | Task | Verification |
| --- | --- | --- |
| Request/Service boundary | 1 | Feature test and route list |
| Inertia table/form boundaries | 2 | Lint, build, and manual checks |
| Safe image storage lifecycle | 1, 3 | `Storage::fake` assertions |
| Landing behavior | 1, 3 | Landing/service test and browser check |
| Durable documentation | 3 | Documentation review |

## Risks and Mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Wrong image removed | High | Store first; persist; remove prior image only after success; test lifecycle. |
| Public image URL breaks | High | Preserve route/filename contract and test success/404 responses. |
| A route caller is missed | Medium | Update routes and module callers together; run route list and build. |
| Authorization is bypassed | Medium | Retain middleware and add service role checks. |

## Open Questions

- None

## Final Verification

- [ ] `php artisan test tests/Feature/CarouselManagementTest.php`
- [ ] `pnpm exec prettier --check resources/js/pages/Carousel`
- [ ] `pnpm exec eslint resources/js/pages/Carousel --max-warnings=0`
- [ ] `pnpm run build`
- [ ] `php artisan route:list --name=carousel`
- [ ] `git diff --check`
- [ ] Manual create, retained-image edit, replacement, status toggle,
  cancelled/confirmed delete, landing visibility, keyboard, and responsive flow.

## Version History

| Version | Date | Author | Notes |
| --- | --- | --- | --- |
| 1.0 | 29 October 2026 | Full-stack Team | Initial PRD from approved implementation plan. |
