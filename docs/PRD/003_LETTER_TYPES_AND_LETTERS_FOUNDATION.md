# PRD 003 — Letter Types and Letters Foundation

**Status:** Draft  
**Created:** 23 October 2026  
**Plan status:** Approved  
**Plan version:** 1  
**Mode:** Backend and documentation

## Overview

Create the reusable database and model foundation for future academic-letter
services. It replaces the retired generic Persuratan direction without
modifying Berkas Sidang Nol.

The foundation introduces Letter Types, a shared Letters transaction table, and
tables for attachments, route history, and audit logs. The first service to use
it later will be Cuti Akademik.

## Background

Six future services need the same core lifecycle, applicant snapshots,
attachments, audit history, configurable approval chains, and manual official
letter numbering:

1. Cuti Akademik
2. Magang
3. Penurunan UKT
4. UKT 50%
5. Pengunduran Diri
6. Bebas Ruang Baca

This is a new architecture based on the approved Letter Backend Architecture.
The new Letter Types master is not the retired Jenis Surat table. Template
Surat is independent from both concepts.

## User stories

- As a developer, I want future letter services to share a dependable data
  foundation without rebuilding lifecycle and audit mechanics.
- As an administrator, I want canonical uppercase roles for future configurable
  approval chains.
- As a future applicant, I want a consistent historical record regardless of
  which letter service I use.

## Scope

- Normalize role names through a production migration.
- Rename WD to WD1, TU to KTU, and Bagian Akademik to AKADEMIK.
- Normalize all other existing names to uppercase.
- Add WD2, WD3, and PRODI, while preserving existing role IDs.
- Create letter_types, letters, letter_attachments, letter_routes, and
  letter_logs tables.
- Add Eloquent models, relations, casts, UUID behavior, keys, and indexes.
- Document all six future services and their reusable shared boundary.
- Use nullable, unique manual letter numbers in Letters.

## Out of scope

- Implementing Cuti Akademik or the other five services.
- Seeding active Letter Type records before each service’s exact chain, notes
  Markdown, PDF template, and service-specific fields are approved.
- Building Letter Type, approval-chain, or Markdown-notes UI.
- Adding create, submit, approve, reject, archive, PDF, upload, or download
  endpoints.
- Migrating Berkas Sidang Nol records into Letters.
- Replacing the existing roles table or adding a second roles table.

## Acceptance criteria

- [ ] Existing role IDs preserve their meaning and current user assignments.
- [ ] Canonical uppercase roles, WD2, WD3, and PRODI exist after production
  migration and fresh seeding.
- [ ] The five reusable Letter tables migrate cleanly on current and fresh
  databases.
- [ ] Letters uses UUID primary keys and rejects duplicate non-null manual
  letter numbers.
- [ ] Deleting a Letter Type referenced by a Letter is restricted.
- [ ] The service catalogue documents all six future services without claiming
  they are implemented.
- [ ] Sidang Nol has no schema, data, route, or behavior change.

## Functional requirements

1. Existing role IDs stay valid; existing users retain their assigned role row.
2. Current roles become MAHASISWA, RESEPSIONIS, DEKAN, WD1, KTU, AKADEMIK,
   BAGIAN LAYANAN, SUPER ADMIN, and ADMIN TTPS.
3. Add WD2, WD3, and PRODI, without depending on their numeric IDs in future
   application access rules.
4. RoleSeeder must create the same canonical role catalogue for fresh
   databases.
5. Letter Types must contain stable unique code, category, name, notes
   Markdown, JSON approval chain, JSON applicant roles, and a PDF-template
   identifier.
6. Letters must have server-generated UUIDs, restricted type ownership,
   applicant/configuration snapshots, separate status and current stage,
   flexible JSON metadata, and manual-number fields.
7. Attachments, routing history, and audit logs must use distinct child tables.
8. A non-null official letter number must be unique in the database.
9. Documentation must make each future service responsible for its own form,
   validation, files, PDF renderer, and transition rules while reusing this
   foundation.

## Non-functional requirements

- Use additive, forward-only migrations and preserve Laravel 12/Eloquent
  conventions.
- Add no routes, UI, queues, APIs, or third-party packages in this foundation.
- Use database keys and indexes to protect shared data integrity.
- Treat UUIDs and Letter Type codes as stable identities, never client-provided
  display labels.

## Edge cases

| Scenario | Expected behavior |
| --- | --- |
| A role already belongs to production users | Its row ID stays unchanged while its name is updated in place. |
| A fresh database is seeded | It receives all canonical roles plus WD2, WD3, and PRODI. |
| A future service reuses an official number | Database uniqueness rejects the duplicate non-null value. |
| A Letter Type has linked Letters | Foreign-key restriction blocks deletion. |
| A future Letter is deleted | Attachment, route, and log rows may cascade; physical-file handling belongs to the future service. |
| Approval configuration is not yet approved | No active Letter Type is seeded with empty or ambiguous rules. |
| Sidang Nol records exist | They are not read, copied, or modified. |

## Existing-system findings

| Area | Evidence | Implementation implication |
| --- | --- | --- |
| Roles | roles contains id, nama, and timestamps; users store numeric role_id | Rename current rows in place and append only new roles. |
| Current role IDs | Existing code treats role_id 1 as student and uses numeric values elsewhere | Preserve IDs 1–9 and avoid assigning future semantics from new IDs. |
| Future architecture | LETTER_BACKEND_ARCHITECTURE.md defines responsibilities | Reuse its foundation boundaries without service-specific workflows. |
| Sidang Nol | Independent model, routes, storage path, and lifecycle | Keep completely outside the Letters domain. |
| Template Surat | Downloadable-template module | Do not link it to Letter Types. |

## Design and architecture

### Ownership

    roles
      └── canonical actors used by future approval chains

    letter_types
      └── configuration for an individual future letter service

    letters
      ├── applicant and configuration snapshots
      ├── status, current stage, manual number, and final PDF metadata
      ├── letter_attachments
      ├── letter_routes
      └── letter_logs

    future service
      └── its form, validation, PDF renderer, and workflow actions

### Future data flow

    authenticated applicant
    → service-specific validation
    → Letter Type configuration lookup
    → server-built letter snapshot and service metadata
    → future attachment, routing, audit, and final-PDF operations

### Decisions

| Decision | Rationale |
| --- | --- |
| Reuse roles | Preserves current user relations and satisfies the product direction. |
| Use uppercase roles.nama in chains | Creates a canonical readable vocabulary without another role system. |
| Append WD2, WD3, and PRODI | Adds workflow actors while preserving current IDs. |
| One letters table | Lets all services share lifecycle, audit, attachment, and number mechanics. |
| UUID letters | Avoids sequential transaction IDs as the domain identity. |
| Manual unique numbers | Matches the approved business rule; no counters are required. |
| No active types yet | New types require valid non-empty chains and applicant roles, which are per-service decisions. |

## Contract and data changes

### Canonical roles

| Existing ID | Canonical name |
| ---: | --- |
| 1 | MAHASISWA |
| 2 | RESEPSIONIS |
| 3 | DEKAN |
| 4 | WD1 |
| 5 | KTU |
| 6 | AKADEMIK |
| 7 | BAGIAN LAYANAN |
| 8 | SUPER ADMIN |
| 9 | ADMIN TTPS |

New rows: WD2, WD3, and PRODI.

### New tables

| Table | Key fields and constraints |
| --- | --- |
| letter_types | Unique code, indexed category, name, notes Markdown, JSON chains/roles, PDF-template key. |
| letters | UUID ID, restricted type ID, applicant snapshots, lifecycle data, nullable unique letter number, JSON metadata, final-PDF state. |
| letter_attachments | Cascade Letter FK, relative path, original name, server-checked MIME/size, nullable uploader. |
| letter_routes | Cascade Letter FK, source/target stages, nullable actor, note, timestamps. |
| letter_logs | Cascade Letter FK, nullable actor, stable action, JSON metadata, timestamps. |

No HTTP API is introduced by this PRD.

## Impacted components

### New files

| File | Purpose |
| --- | --- |
| database/migrations/{timestamp}_normalize_roles_and_add_letter_workflow_roles.php | Rename existing roles in place and add workflow roles. |
| database/migrations/{timestamp}_create_letter_types_table.php | Create Letter Type configuration. |
| database/migrations/{timestamp}_create_letters_table.php | Create UUID letter records and indexes. |
| database/migrations/{timestamp}_create_letter_attachments_table.php | Create attachment metadata. |
| database/migrations/{timestamp}_create_letter_routes_table.php | Create route history. |
| database/migrations/{timestamp}_create_letter_logs_table.php | Create audit logs. |
| app/Models/LetterType.php | Letter Type model, casts, relations. |
| app/Models/Letter.php | UUID Letter model, casts, relations. |
| app/Models/LetterAttachment.php | Attachment metadata relation. |
| app/Models/LetterRoute.php | Route-history relation. |
| app/Models/LetterLog.php | Audit-log relation. |
| docs/modules/letter-services.md | Six-service catalogue and shared boundary. |
| tests/Feature/LetterFoundationTest.php | Role, schema, UUID, and manual-number tests. |
| docs/PRD/003_LETTER_TYPES_AND_LETTERS_FOUNDATION.md | This PRD. |

### Modified files

| File | Change |
| --- | --- |
| database/seeders/RoleSeeder.php | Seed canonical uppercase names and added workflow roles. |
| routes/web.php | Let Dashboard support non-student roles without a fixed historical role range. |
| app/Http/Controllers/DashboardController.php | Redirect students to Sidang Nol. |
| docs/README.md | Link Letters foundation and service catalogue. |
| docs/architecture.md | Describe the reusable Letters boundary. |
| docs/data-model.md | Document the five Letter tables. |
| docs/auth-authorization.md | Document canonical and added roles. |

## Approved implementation tasks

### Task 1 — Normalize roles and fresh-data seeding

Create an in-place role migration and update RoleSeeder.

Acceptance criteria:

- IDs 1–9 remain their current rows.
- All approved uppercase names exist.
- WD2, WD3, and PRODI exist after migration and fresh seeding.

Dependencies: None.

Verification: php artisan test --filter=LetterFoundationTest and
php artisan migrate:fresh --seed --env=testing.

Risk and rollback: Never delete or reorder production role rows. Any rollback
of names uses an explicit prior-name mapping.

### Task 2 — Create shared schema and models

Create the five tables and models with documented relations, casts, UUIDs,
foreign keys, and indexes.

Acceptance criteria:

- Existing and fresh databases migrate successfully.
- Letters use UUID string primary keys.
- Referenced Letter Types cannot be deleted.
- Duplicate non-null manual numbers are rejected.

Dependencies: Task 1.

Verification: php artisan test --filter=LetterFoundationTest and
php artisan migrate:fresh --seed --env=testing.

Risk and rollback: Tables are additive. No service data or Sidang Nol data is
created or modified.

### Task 3 — Document future-service boundaries

Create the six-service catalogue and update architecture, data-model, role,
and documentation-index references.

Acceptance criteria:

- Every service is named and clearly marked not implemented.
- Documentation assigns service-specific form, validation, attachments, PDF,
  and workflow rules to each future service.
- Letter Types, legacy Jenis Surat, and Template Surat are distinguished.

Dependencies: Tasks 1 and 2.

Verification: git diff --check.

### Task 4 — Verify the foundation

Run focused tests, complete Laravel tests, fresh migration/seed checks, and the
frontend production build.

Acceptance criteria:

- New foundation tests and current application tests pass.
- Fresh migration/seed succeeds.
- Sidang Nol regressions are absent.

Dependencies: Tasks 1–3.

Verification: php artisan test; pnpm build;
php artisan migrate:fresh --seed --env=testing; git diff --check.

## Requirement coverage

| Requirement | Task | Verification |
| --- | --- | --- |
| Preserve role identities while normalizing names | 1 | Focused role assertions and fresh seed check |
| Add PRODI, WD2, and WD3 | 1 | Migration and seeder assertions |
| Create reusable five-table foundation | 2 | Schema/model feature tests |
| Enforce manual-number uniqueness | 2 | Duplicate-number test |
| Keep Sidang Nol unchanged | 2 and 4 | Existing tests and regression review |
| Document six services | 3 | Documentation review |
| Verify foundation | 4 | Tests, build, migrations, diff check |

## Risks and mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Role-ID reassignment changes permissions | High | Update names in place and append only new rows. |
| Ambiguous approval configuration | High | Do not seed active Letter Types until each service is approved. |
| Duplicate manual official numbers | High | Enforce nullable unique database index. |
| Future services duplicate core lifecycle logic | Medium | Document the ownership boundary before Cuti Akademik begins. |
| Sidang Nol is coupled accidentally | High | No foundation migration, route, controller, or data operation references it. |

## Open questions

- None.

## Final verification

- [ ] Existing user role assignments retain their IDs.
- [ ] All canonical and new roles exist.
- [ ] New tables migrate on existing and fresh databases.
- [ ] UUID identities and unique manual numbers are verified.
- [ ] No active Letter Type is seeded without approved configuration.
- [ ] Documentation lists the six future services and shared boundary.
- [ ] php artisan test passes.
- [ ] pnpm build passes.
- [ ] git diff --check passes.

