# Users Module Alignment

**Status**: Complete
**Version**: 1.1
**Plan Status**: Implemented
**Plan Version**: 2
**Mode**: full-stack
**Created**: 28 October 2026
**Completed**: 28 October 2026

## Overview

Align Users with the SIARSIP Vue/Inertia and Laravel Request/Service conventions. A single `/users` endpoint serves Pegawai by default and Mahasiswa through `audience=mahasiswa`.

## Scope

- Add Users local DataTable, components, and TypeScript support boundaries.
- Replace direct Axios, direct SweetAlert, and page-owned EasyDataTable state.
- Merge the two list actions into one audience-filtered index action.
- Add UserRequest, UserService, focused tests, and documented authorization behavior.

## Out of Scope

- Schema, role, permission, or fixed reset-password business-rule changes.
- New dependencies or frontend test tooling.

## Acceptance Criteria

- [x] `/users` defaults to Pegawai and supports `?audience=mahasiswa`.
- [x] List, form, show, delete, and reset use documented ownership boundaries.
- [x] Reset remains server-restricted to roles 6 and 8.
- [x] Existing role IDs and account fields remain compatible.

## Implementation Tasks

1. Create the Users local table, typed support files, and clean detail component.
2. Refactor Index, Form, and Show to shared controls, alerts, and Inertia state.
3. Add UserRequest/UserService, merge list filtering, remove `/users-mahasiswa`, and test permissions.
4. Update authorization documentation and run focused test, lint, format, build, and diff checks.

## Final Verification

- [x] `pnpm exec prettier --check resources/js/pages/Users`
- [x] `pnpm exec eslint resources/js/pages/Users --max-warnings=0`
- [x] `pnpm run build`
- [x] `php artisan test tests/Feature/UserManagementTest.php` — 3 passed, 11 assertions.
- [x] `git diff --check`

## Completion Notes

- The unified Users index is implemented through `UserRequest` and
  `UserService`, using the canonical `datatable()`, `create()`, `update()`,
  `delete()`, and domain-specific `resetPassword()` service methods.
- `docs/backend-development-base.md` now defines this service-method contract
  as mandatory for new work and touched module refactors.
