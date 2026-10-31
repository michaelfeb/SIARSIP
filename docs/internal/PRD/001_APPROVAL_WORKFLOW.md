# Alur Persetujuan Surat

**Status**: Implemented
**Version**: 1.1
**Plan Status**: Implemented
**Plan Version**: 1.1
**Mode**: full-stack
**Requirement Source**: Approved implementation plan handoff in chat

---

## Overview

Build `Pengaturan → Alur Persetujuan Surat` for authorized administrators to configure each Letter Type’s ordered approval chain and process notes safely. For Cuti Akademik, RESEPSIONIS finalizes the PDF and may select its signer from any role in that letter's approval chain; the role immediately before RESEPSIONIS is the default selection.

## Background

`letter_types` already stores `approval_chain` and `notes_markdown`, but there is no administrative editor. Cuti Akademik currently finalizes at RESEPSIONIS and hard-codes DEKAN as its signatory role.

## User Story

- As an authorized administrator, I want to configure a letter type’s workflow and notes so that new submissions use the correct process.
- As a Cuti Akademik finalizer, I want to select a signer role from the configured approval chain, with the predecessor role preselected, so that the generated letter uses the intended authority.

## Scope

- New settings submenu, Inertia page, Laravel controller/request/service, and feature tests.
- Ordered, unique, master-role-backed approval chains.
- Locked terminal `RESEPSIONIS`, active-workflow blocking, Markdown notes editing/preview.
- Cuti finalization signer-role selection, validation, and UI controls.
- Transactional protection against concurrent workflow changes and submissions.

## Out of Scope

- Creating or deleting master roles.
- Changing archived PDFs or archived signer snapshots.
- Changing a future letter module’s PDF template/finalization rules.
- Adding a third-party rich-text or drag-and-drop dependency.

## Acceptance Criteria

- [ ] Authorized admins can select a Letter Type and save its workflow and notes.
- [ ] Stages are existing, non-duplicate roles.
- [ ] Terminal RESEPSIONIS cannot move or be deleted.
- [ ] Types with `IN_PROGRESS` letters reject all configuration changes.
- [ ] Cuti defaults the signer role to the role immediately before terminal RESEPSIONIS.
- [ ] Cuti finalizers can select any role in the configured approval chain.
- [ ] Backend rejects a signer role outside that chain or a user outside the selected role.
- [ ] Archived letters retain their signer snapshot.
- [ ] Targeted tests, formatting, and production build pass.

## Functional Requirements

- Managers are existing roles 6 (AKADEMIK) and 8 (SUPER ADMIN).
- A terminal RESEPSIONIS remains terminal; additions are placed before it.
- Draft, rejected, and archived letters do not block updates.
- Cuti’s configured chain must contain at least one stage before terminal RESEPSIONIS so the default signer role can be derived.
- The selected signer role is request-scoped: it is not stored on `letter_types` and does not alter the approval chain.
- Notes are Markdown with safe server-rendered preview.

## Non-Functional Requirements

- Server-side authorization and validation are authoritative.
- Workflow updates and submissions use transactions and row locks.
- No migration is required.
- Existing unrelated Dashboard worktree changes remain untouched.

## Edge Cases

| Scenario                                            | Expected behavior                                                    |
| --------------------------------------------------- | -------------------------------------------------------------------- |
| Unknown or duplicate role                           | Validation fails without persistence.                                |
| Terminal RESEPSIONIS is removed or reordered        | Validation fails without persistence.                                |
| Active letter exists                                | Update fails with count/message and no partial write.                |
| Draft is submitted during update                    | Type lock serializes both flows; submission uses one complete chain. |
| Cuti has no predecessor before terminal RESEPSIONIS | Configuration fails.                                                 |
| Signer role outside approval chain or wrong user role | Finalization rejects it.                                            |
| Archived letter after later workflow edit           | Snapshot/PDF remain unchanged.                                       |

## Existing-System Findings

| Area                 | Evidence                                                      | Planning implication                  |
| -------------------- | ------------------------------------------------------------- | ------------------------------------- |
| Letter configuration | `app/Models/LetterType.php`                                   | Reuse existing fields; no migration.  |
| Workflow             | `app/Services/LetterService.php`                              | Add type lock during submission.      |
| Cuti signer          | `app/Services/CutiAkademikService.php`                        | Replace fixed DEKAN resolution.       |
| Finalization UI      | `resources/js/pages/CutiAkademik/Ajuan.vue`                   | Use server-provided role label.       |
| Settings             | `routes/web.php`, `resources/js/components/AppSidebarMenu.ts` | Reuse roles 6/8 and existing submenu. |

## Frontend Stack Profile

Vue 3.5 + Inertia 3 + TypeScript + Tailwind 4, with local shadcn-style controls and Vite 8. Requests use Inertia `useForm`; no migration is authorized.

## Design and Architecture

### Ownership

`ApprovalWorkflowService` owns Letter Type configuration. `LetterService` owns submission locking. `CutiAkademikService` owns Cuti signatory resolution. Vue module-local components own the settings UI.

### Flow

GET renders Letter Types, roles, chains, notes, and active counts. PUT validates and locks the type, rejects active workflows, then updates chain/notes atomically. At Cuti finalization, RESEPSIONIS selects a signer role from the configured chain (default: the predecessor stage), selects a user in that role, and the chosen role/user are snapshotted in the archived Letter.

### Decisions and Rationale

| Decision                             | Rationale                                                                                                  |
| ------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| Native drag and Markdown toolbar     | Meets the visual behavior without new dependencies and preserves `notes_markdown`.                         |
| Reuse existing `letter_types` fields | The necessary persistence already exists.                                                                  |
| Lock Letter Type in save and submit  | Prevents mixed/stale workflow transitions.                                                                 |
| Cuti remains terminal at RESEPSIONIS | Preserves its existing finalization contract while allowing other future types to use different workflows. |

## Contract and Data Changes

- `GET /alur-persetujuan-surat`
- `PUT /alur-persetujuan-surat/{letterType}`
- Authentication: `auth`, roles `6,8`.
- Request: `approval_chain: string[]`, `notes_markdown: string|null`.
- No schema, event, or public API migration.

## Impacted Components

### New Files

| File                                                                                 | Purpose                                                   |
| ------------------------------------------------------------------------------------ | --------------------------------------------------------- |
| `app/Http/Controllers/ApprovalWorkflowController.php`                                | HTTP/Inertia boundary.                                    |
| `app/Http/Requests/ApprovalWorkflowRequest.php`                                      | Input validation and payload helpers.                     |
| `app/Services/ApprovalWorkflowService.php`                                           | Workflow configuration queries and transactional updates. |
| `resources/js/pages/AlurPersetujuanSurat/Index.vue`                                  | Settings page coordinator.                                |
| `resources/js/pages/AlurPersetujuanSurat/components/ApprovalWorkflowStages.vue`      | Ordered stage editor.                                     |
| `resources/js/pages/AlurPersetujuanSurat/components/ApprovalWorkflowNotesEditor.vue` | Markdown notes editor and preview.                        |
| `resources/js/pages/AlurPersetujuanSurat/ts/approvalWorkflow.types.ts`               | Module types.                                             |
| `tests/Feature/ApprovalWorkflowTest.php`                                             | Backend workflow-setting coverage.                        |
| `docs/modules/alur-persetujuan-surat.md`                                             | Module contract.                                          |

### Modified Files

| File                                                       | Change                                       |
| ---------------------------------------------------------- | -------------------------------------------- |
| `routes/web.php`                                           | Settings routes and access middleware.       |
| `resources/js/components/AppSidebarMenu.ts`                | New Pengaturan submenu entry.                |
| `app/Services/LetterService.php`                           | Lock/re-read Letter Type at submission.      |
| `app/Services/CutiAkademikService.php`                     | Dynamic signer role.                         |
| `app/Http/Controllers/CutiAkademikController.php`          | Pass finalization signer context.            |
| `resources/js/pages/CutiAkademik/Ajuan.vue`                | Dynamic signer/finalization labels.          |
| `resources/js/pages/CutiAkademik/ts/cutiAkademik.types.ts` | Finalization context type.                   |
| `resources/views/letters/cuti-akademik.blade.php`          | Dynamic signatory role caption in final PDF. |
| `tests/Feature/CutiAkademikTest.php`                       | Dynamic signer coverage.                     |
| `docs/modules/cuti-akademik.md`                            | Dynamic signer documentation.                |

## Approved Implementation Tasks

### Task 1 — Backend workflow settings module

Create routes, controller, Form Request, and service. Return page data, validate role existence and uniqueness, enforce terminal-stage protection, active-letter blocks, and transactional persistence.

Verification: `php artisan test tests/Feature/ApprovalWorkflowTest.php`; `vendor/bin/pint --test`.

### Task 2 — Prevent workflow-change/submission races

Lock and re-read Letter Type during submission before the first configured stage is selected.

Verification: add state-integrity coverage to `tests/Feature/ApprovalWorkflowTest.php`.

### Task 3 — Configurable Cuti Akademik signer at finalization

Use the stage before terminal RESEPSIONIS as the default signer role. Let RESEPSIONIS select any role from the chain and a user with that role; validate both values server-side and preserve the selected role/user snapshot.

Verification: `php artisan test tests/Feature/CutiAkademikTest.php`.

### Task 4 — Workflow settings interface

Build the responsive settings page, drag/reorder controls, role addition, locked-state feedback, Markdown toolbar/preview, errors, and save state.

Verification: `pnpm exec eslint resources/js/pages/AlurPersetujuanSurat resources/js/components/AppSidebarMenu.ts`; `pnpm run build`; manual responsive and keyboard checks.

### Task 5 — Cuti finalization UI and navigation

Add the Pengaturan submenu and use server-provided finalization context for the role selector, default signer role, and matching signer list.

Verification: `pnpm run build`; manual authorization and Cuti finalization path.

### Task 6 — Documentation and full regression

Document the module and run full backend/frontend verification.

Verification: `php artisan test`; `vendor/bin/pint --test`; `pnpm run format:check`; `pnpm run build`.

## Requirement Coverage

| Requirement                           |    Task | Verification               |
| ------------------------------------- | ------: | -------------------------- |
| Configurable chains and notes         |    1, 4 | Feature test and manual UI |
| Existing unique roles                 |       1 | Feature test               |
| Protected Receptionist                |    1, 4 | Feature test and manual UI |
| Active-workflow block and race safety | 1, 2, 4 | Feature test               |
| Configurable signer/final PDF history |    3, 5 | Cuti tests                 |
| Navigation and access                 |    1, 5 | HTTP/manual check          |

## Risks and Mitigations

| Risk                         | Mitigation                                              |
| ---------------------------- | ------------------------------------------------------- |
| Stranded active letters      | Reject configuration while any letter is active.        |
| Race condition               | Lock Letter Type in both settings and submission flows. |
| Forged signer                | Validate in the service.                                |
| Historical document mutation | Preserve current signatory snapshot.                    |

## Open Questions

- None

## Final Verification

- [ ] `php artisan test tests/Feature/ApprovalWorkflowTest.php tests/Feature/CutiAkademikTest.php`
- [ ] `php artisan test`
- [ ] `vendor/bin/pint --test`
- [ ] `pnpm run format:check`
- [ ] `pnpm run build`
- [ ] Manual browser, responsive, keyboard, and direct-route authorization checks.

## API Reference

The settings module uses the documented GET and PUT routes under Contract and Data Changes. Its successful PUT response redirects back to the settings page; validation failures use Laravel’s Inertia validation error contract.

## Version History

| Version | Date       | Author          | Notes                                         |
| ------- | ---------- | --------------- | --------------------------------------------- |
| 1.0     | 2026-10-29 | Full-stack Team | Initial PRD from approved implementation plan |
