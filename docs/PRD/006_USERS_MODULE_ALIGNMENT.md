# Users Module Alignment

**Status**: Draft
**Version**: 1.0
**Plan Status**: Approved
**Plan Version**: 2
**Mode**: full-stack
**Created**: 28 October 2026

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

- [ ] `/users` defaults to Pegawai and supports `?audience=mahasiswa`.
- [ ] List, form, show, delete, and reset use documented ownership boundaries.
- [ ] Reset remains server-restricted to roles 6 and 8.
- [ ] Existing role IDs and account fields remain compatible.

## Implementation Tasks

1. Create the Users local table, typed support files, and clean detail component.
2. Refactor Index, Form, and Show to shared controls, alerts, and Inertia state.
3. Add UserRequest/UserService, merge list filtering, remove `/users-mahasiswa`, and test permissions.
4. Update authorization documentation and run focused test, lint, format, build, and diff checks.

## Final Verification

- `pnpm exec prettier --check resources/js/pages/Users`
- `pnpm exec eslint resources/js/pages/Users --max-warnings=0`
- `pnpm run build`
- `php artisan test tests/Feature/UserManagementTest.php`
- `git diff --check`
