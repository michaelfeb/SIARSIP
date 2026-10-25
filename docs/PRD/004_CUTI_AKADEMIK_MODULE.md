# PRD 004 — Cuti Akademik Module

**Status:** Implemented — pending production deployment  
**Created:** 24 October 2026  
**Plan status:** Implemented  
**Plan version:** 1  
**Mode:** Full stack

## Overview

Implement Cuti Akademik as the first active service on the reusable Letters
foundation from PRD 003. A mahasiswa can prepare, attach evidence to, submit,
track, and download a Cuti Akademik request. The request follows a fixed
approval chain and is finally numbered and generated as a private PDF by
Resepsionis.

This module is a new Letter Type. It does not modify, migrate, or replace
Berkas Sidang Nol, Template Surat, or retained legacy files.

## Background

The application has reusable Letter Type, Letter, attachment, route-history,
and audit-log tables, but no active service. Cuti Akademik is the first of six
approved services and establishes the reusable letter-module pattern.

Accepted product facts:

- Only MAHASISWA may create a request.
- Applicant identity is taken from authenticated biodata; the client never
  owns the authoritative applicant name, NIM, or programme.
- The service metadata is semester, tahun akademik, nomor hp, nominal ukt, and
  alasan cuti.
- Supporting evidence is mandatory: one to five PDF, JPG, JPEG, or PNG files,
  each at most 100 KB.
- The fixed chain is PRODI → AKADEMIK → KTU → DEKAN → RESEPSIONIS.
- Each stage can forward only to its immediate next stage; skipping is not
  permitted.
- DEKAN is the final approver/signatory. RESEPSIONIS manually enters the
  official number, generates the PDF, and archives the request.

## User stories

- As a mahasiswa, I can submit a Cuti Akademik request using existing biodata
  so I do not re-enter my identity.
- As an approver, I can inspect the request and evidence before forwarding or
  rejecting it at my stage.
- As RESEPSIONIS, I can enter the formal number and generate the approved PDF
  for the student.
- As a developer, I can reuse attachment, route, audit, and UI boundaries for
  later Letter services without making Cuti rules global.

## Scope

### Included

- Activate one idempotent CUTI_AKADEMIK Letter Type for fresh and existing
  databases.
- Implement Cuti routes, controller, Form Requests, service, record
  authorization, and feature tests.
- Provide create/edit draft, submit, list/datatable, show, staff review,
  forward, reject, finalize, attachment streaming, and final-PDF download.
- Store evidence privately in letter_attachments and final output in the
  Letter final_pdf_path.
- Add a small shared Letter attachment-upload section for this and future
  Letter modules.
- Add Cuti Index.vue, DataTable.vue, Form.vue, Show.vue, and Ajuan.vue using
  the documented Berkas Sidang Nol module conventions.
- Add Akademik → Cuti Akademik to the sidebar for applicable applicant,
  reviewer, and administrator roles.
- Generate the final document synchronously with the installed DomPDF package.
- Update the Letter-service, data-model, authorization, and development-base
  documentation.

### Excluded

- Any change to Berkas Sidang Nol data, files, routes, decisions, or UI.
- A Letter Type administration UI, approval-chain editor, or Markdown-notes
  editor.
- Magang, Penurunan UKT, UKT 50%, Pengunduran Diri, and Bebas Ruang Baca.
- Queues, new PDF dependencies, digital signatures, QR verification, and
  automatic official-number allocation.
- Public file/PDF URLs.
- Coupling Template Surat to Letter Types.
- Generic repositories, base controllers, or base CRUD services.

## Functional requirements

### Letter Type configuration

Create a CutiAkademikLetterTypeSeeder. It creates or updates exactly one active
Letter Type with this configuration:

| Field | Value |
| --- | --- |
| code | CUTI_AKADEMIK |
| category | AKADEMIK |
| name | Cuti Akademik |
| applicant_roles | ["MAHASISWA"] |
| approval_chain | ["PRODI", "AKADEMIK", "KTU", "DEKAN", "RESEPSIONIS"] |
| pdf_template | letters.cuti-akademik |
| notes_markdown | The approved guidance in this PRD. |

DatabaseSeeder calls the seed for fresh environments. A forward-only,
idempotent data migration applies the same configuration to an existing
production database. Neither operation may duplicate the Letter Type.

### Applicant form and drafts

The form displays read-only authenticated biodata: Nama Mahasiswa, NIM, and
Program Studi. The editable values are:

| Field | Storage | Validation |
| --- | --- | --- |
| Semester | meta.semester | Required academic-semester value |
| Tahun Akademik | meta.tahun_akademik | Required academic-year value |
| Nomor Kontak | meta.nomor_hp | Required active phone/WhatsApp number |
| Nominal UKT | meta.nominal_ukt | Required non-negative tuition amount |
| Tujuan | meta.alasan_cuti and Letter purpose | Required leave reason |

CutiAkademikService builds server-owned applicant, category, and notes
snapshots. It creates an editable DRAFT. Only its applicant can view, update,
delete, or submit the draft. Submitted records cannot be edited through the
draft form.

### Supporting documents

Supporting documents are optional. A request contains no more than five files;
every file must be PDF, JPG, JPEG, or PNG and at most 100 KB.

Browser validation is helpful only. Form Requests and LetterAttachmentService
are authoritative. Each accepted file uses an opaque private path such as:

    letters/attachments/{year}/user-{applicant_id}/letter-{letter_uuid}/{uuid}.{extension}

letter_attachments stores only relative path, original filename, server-detected
MIME type, byte size, and uploader. An authorized endpoint receives the Letter
UUID and attachment ID; it never receives or streams an arbitrary raw path.

The shared frontend LetterAttachmentUpload component accepts files through
v-model, presents limits and client errors, and supports an explicit
required/optional presentation. It contains no Cuti routes or workflow logic;
Form.vue owns the module messaging and submission.

### Lifecycle and approval chain

| Status | Meaning |
| --- | --- |
| DRAFT | Applicant-owned, editable/deletable, not visible to reviewers. |
| IN_PROGRESS | Submitted and awaiting the role in current_stage. |
| REJECTED | Rejected by an eligible reviewer; immutable historical record. |
| ARCHIVED | Final PDF stored successfully; immutable completed record. |

Submitting a valid draft sets status to IN_PROGRESS, current_stage to PRODI,
and submitted_at. It adds a route from null to PRODI and an audit log.

| Current stage | Eligible role | Successful forward destination |
| --- | --- | --- |
| PRODI | PRODI | AKADEMIK |
| AKADEMIK | AKADEMIK | KTU |
| KTU | KTU | DEKAN |
| DEKAN | DEKAN | RESEPSIONIS |
| RESEPSIONIS | RESEPSIONIS | Finalise and archive; no next stage |

The service derives the only valid next stage from the ordered seeded chain;
a supplied, skipped, stale, or otherwise invalid destination fails. Each
forward stores a LetterRoute and LetterLog entry. An eligible reviewer may
reject with a note; rejection records actor, stage, and note, changes status to
REJECTED, clears current_stage, and preserves all history and files.

PRODI sees only requests from students in the same Program Studi. Other chain
roles see requests waiting at their own stage. SUPER ADMIN may inspect the
module but cannot bypass a workflow stage unless it holds the matching workflow
role.

### Finalisation and PDF

Only a Letter currently at RESEPSIONIS may finalize. Finalization requires:

- a non-empty manually entered official letter number;
- a selected active DEKAN signatory; and
- an optional decision/finalisation note.

The service validates that the signatory currently has role DEKAN, then stores
a signatory snapshot for document rendering. The finalisation is one locked
workflow:

    Lock the Letter row
    → re-check Letter Type, status, and RESEPSIONIS stage
    → validate the unique official number and DEKAN signatory
    → render PDF from stored Letter values and signer snapshot
    → write PDF bytes to the secure disk
    → save number, issued date, signer snapshot, final path, ARCHIVED state
    → append final route/log records
    → commit

`PdfGeneratorService` is the shared DomPDF renderer. `CutiAkademikService`
configures it with `resources/views/letters/cuti-akademik.blade.php` and passes
stored Letter values, never raw HTTP input. `LetterService` owns the shared
finalisation/archive workflow. A final file uses an opaque path:

    letters/final/{year}/{month}/{letter_uuid}-CUTI_AKADEMIK.pdf

If PDF rendering or storage fails, finalisation must not archive the Letter or
consume its manual number. The service stores safe diagnostic state, removes
only the newly-created orphan file if present, and leaves the request available
for an authorized retry. An ARCHIVED applicant can download the final PDF;
authorized staff use the same record-level policy.

### Authorization and backend boundary

Routes use auth and broad role middleware. A Letter policy and
CutiAkademikService enforce ownership, Letter Type identity, stage role,
programme, and state again for each record action. Menu visibility is never an
authorization rule.

- MAHASISWA may access only their own Cuti Letters.
- Reviewers may access only matching current-stage Letters; PRODI also requires
  matching Program Studi.
- RESEPSIONIS finalizes only its current stage.
- Direct URLs cannot expose another Letter Type, another student’s request,
  a draft review, or a private file/PDF without Letter-level authorization.

The implementation follows the documented boundary:

    Route → Controller → Form Request → module Service → models/storage/PDF

Controllers remain thin. CutiAkademikService owns all Cuti queries and
transitions. LetterAttachmentService is shared because all future Letters use
the same attachment table/private-storage contract. The PDF generator remains
Cuti-specific because its document content belongs to the service.

### Frontend pages and behavior

Create this structure:

    resources/js/pages/CutiAkademik/
    ├── components/
    │   ├── CutiAkademikAttachmentList.vue
    │   ├── CutiAkademikDetails.vue
    │   └── CutiAkademikStatusBadge.vue
    ├── ts/
    │   ├── cutiAkademik.capabilities.ts
    │   ├── cutiAkademik.constants.ts
    │   └── cutiAkademik.types.ts
    ├── __testing__/
    │   └── first Cuti frontend test files when added
    ├── Ajuan.vue
    ├── DataTable.vue
    ├── Form.vue
    ├── Index.vue
    └── Show.vue

    resources/js/components/letters/
    └── LetterAttachmentUpload.vue

The UI uses the established frontend base:

- Index.vue owns heading, top-level actions, Inertia query/navigation, and
  mutations. Its local DataTable.vue owns Cuti search/filter controls, headers,
  cells, status badges, action controls, and emitted user intents.
- The existing generic data-table wrapper remains the visual base. Data columns
  size naturally; only Aksi has a fixed width appropriate to known actions.
- The list uses server filtering/pagination and preserves responsive horizontal
  overflow rather than hiding data.
- Form.vue uses shared Input, Label, Select, and Button controls, displays
  server-built biodata, fields, validation errors, and the shared upload
  section.
- Show.vue uses clean detail rows, Markdown notes, route history, and
  authorized file links/previews without exposing file paths.
- Ajuan.vue uses the approved two-section layout: details/documents on the
  left, sticky decision panel on the right at xl widths, and a stacked layout
  below xl. The final Resepsionis panel includes number, DEKAN signatory,
  optional note, Batal, Tolak, and Terima & Generate Surat.
- Template event handlers use on… names. Exported helpers retain the full
  CutiAkademik domain name.
- Shared controls retain rounded-lg, the shared Input/Select, and the
  documented muted-orange primary button standard.

The sidebar gets a new Akademik parent with Cuti Akademik below it. It appears
for MAHASISWA and applicable reviewer/admin roles, without removing existing
Berkas Sidang Nol navigation.

### Approved notes Markdown

The seed must store this content, apart from ordinary source-code escaping:

    Panduan Pengisian Surat Cuti Akademik

    **Tujuan surat ini:**

    Memberikan izin resmi kepada mahasiswa untuk mengambil cuti akademik selama periode tertentu tanpa kehilangan status sebagai mahasiswa aktif.

    **Data yang diperlukan:**

    1. **Nama Mahasiswa**: Nama lengkap sesuai kartu identitas (otomatis terisi)
    2. **NIM**: Nomor Induk Mahasiswa (otomatis terisi)
    3. **Program Studi**: Program studi mahasiswa (otomatis terisi)
    4. **Nomor Kontak**: Nomor telepon atau WhatsApp yang aktif
    5. **Nominal UKT**: Nominal UKT/Biaya pendidikan per semester
    6. **Tujuan**: Alasan pengambilan cuti akademik (misal: sakit, keperluan keluarga, dll)

    **Catatan penting:**

    1. Cuti akademik hanya berlaku untuk 1 semester
    2. Mahasiswa dapat mengambil maksimal 2 semester cuti berturut-turut
    3. Setelah cuti berakhir, mahasiswa wajib mendaftar ulang semester berikutnya
    4. Nomor kontak harus aktif untuk komunikasi lebih lanjut
    5. Dokumen pendukung seperti surat sakit dari rumah sakit atau surat keterangan keluarga dapat dilampirkan bila tersedia

    **Alur persetujuan:**

    1. **PRODI**: Verifikasi kelengkapan data dan riwayat akademik mahasiswa
    2. **AKADEMIK**: Verifikasi kelengkapan administratif dan status pembayaran
    3. **KTU**: Verifikasi
    4. **DEKAN**: Persetujuan final
    5. **RESEPSIONIS**: Nomor surat, generate PDF, dan pengarsipan

    **Timeline persetujuan:**

    1. PRODI: 1 hari kerja
    2. AKADEMIK: 1 hari kerja
    3. KTU: 1 hari kerja
    4. DEKAN: 1 hari kerja
    5. RESEPSIONIS: 1 hari kerja

    **Setelah disetujui:**

    Surat akan di-generate otomatis dalam format PDF dengan nomor surat resmi. Mahasiswa dapat mengunduh dan mencetak untuk keperluan administratif.

## Non-functional requirements

- Preserve Laravel 12, Inertia Vue 3, TypeScript, Tailwind, existing shared UI
  components, and barryvdh/laravel-dompdf; add no dependency.
- Use forward-only migrations. The PRD 003 Letter foundation remains the only
  shared schema; there is no Cuti transaction table.
- Use transactions and row locks for multi-write workflows, particularly
  transition/finalisation and attachment cleanup.
- Use opaque private paths and server-detected MIME metadata; never construct a
  public URL or storage path from a user filename.
- Preserve historical snapshots, attachments, routes, and logs after submission.
  Later user-profile edits do not rewrite Letter data.
- Preserve keyboard accessibility, responsive layouts, and the documented
  shared visual controls.

## Existing-system findings

| Area | Evidence | Implementation effect |
| --- | --- | --- |
| Letter foundation | Letter, LetterType, LetterAttachment, LetterRoute, and LetterLog already exist | Reuse these tables/models; do not add a Cuti main table. |
| Roles | Canonical roles include MAHASISWA, PRODI, AKADEMIK, KTU, DEKAN, RESEPSIONIS | Use readable role names in the seeded chain and service checks. |
| Private disk | secure maps to storage/app/secure_storage with private visibility | Store evidence and final PDFs exclusively there. |
| PDF support | DomPDF is installed and used by Sidang Nol | Use it through a Cuti-specific generator; do not add a package. |
| Sidebar | AppSidebarMenu.ts maps role IDs to groups | Add Akademik/Cuti navigation; keep server authorization authoritative. |
| Frontend base | Berkas Sidang Nol documents local pages/table/components/ts and responsive layouts | Follow its module boundary without copying its domain logic. |
| Current Letter Types | PRD 003 seeds no active type | Activate only the approved Cuti Letter Type. |

## Route and page contract

Use the route-name prefix cuti-akademik and Letter UUIDs.

| Intent | Endpoint | Form Request / service action |
| --- | --- | --- |
| Index | GET /cuti-akademik | index filters → datatable() |
| Create | GET /cuti-akademik/create | server-built applicant/type props |
| Store draft | POST /cuti-akademik | CutiAkademikRequest → create() |
| Show | GET /cuti-akademik/{letter} | findForDisplay() and policy |
| Edit | GET /cuti-akademik/{letter}/edit | draft ownership guard |
| Update | PUT /cuti-akademik/{letter} | CutiAkademikRequest → update() |
| Delete draft | DELETE /cuti-akademik/{letter} | delete() |
| Submit | PUT /cuti-akademik/{letter}/submit | `ajuan()` service action |
| Review page | GET /cuti-akademik/{letter}/ajuan | stage guard and page props |
| Forward or reject | POST /cuti-akademik/{letter}/decision | CutiAkademikRequest → decide() |
| Finalize | POST /cuti-akademik/{letter}/finalise | CutiAkademikRequest → finalize() |
| Attachment access | GET /cuti-akademik/{letter}/attachments/{attachment} | policy → stream |
| Final PDF | GET /cuti-akademik/{letter}/download | policy → download |

## Impacted components

### New files

| File | Purpose |
| --- | --- |
| app/Http/Controllers/CutiAkademikController.php | Thin Inertia/file controller. |
| app/Http/Requests/CutiAkademikRequest.php | Route-aware validation for list filters, drafts, decisions, and finalisation. |
| app/Services/LetterService.php | Abstract shared Letter workflow, authorization, locking, and audit foundation. |
| app/Services/CutiAkademikService.php | Cuti queries, snapshots, lifecycle, and workflow invariants. |
| app/Services/PdfGeneratorService.php | Shared DomPDF renderer for a Letter Type's configured Blade view. |
| app/Services/LetterAttachmentService.php | Shared private attachment persistence/cleanup. |
| app/Policies/LetterPolicy.php | Letter-level ownership, stage, and file/PDF checks. |
| database/seeders/CutiAkademikLetterTypeSeeder.php | Idempotent Letter Type configuration. |
| database/migrations/{timestamp}_seed_cuti_akademik_letter_type.php | Production-safe Letter Type activation. |
| resources/views/letters/cuti-akademik.blade.php | Final official PDF view. |
| resources/js/components/letters/LetterAttachmentUpload.vue | Shared selected-file section. |
| resources/js/pages/CutiAkademik/Index.vue | List-page coordinator. |
| resources/js/pages/CutiAkademik/DataTable.vue | Local filters, cells, badges, and actions. |
| resources/js/pages/CutiAkademik/Form.vue | Create/edit form. |
| resources/js/pages/CutiAkademik/Show.vue | Read-only details/history. |
| resources/js/pages/CutiAkademik/Ajuan.vue | Staff review/finalisation page. |
| resources/js/pages/CutiAkademik/components/CutiAkademikDetails.vue | Clean details layout. |
| resources/js/pages/CutiAkademik/components/CutiAkademikAttachmentList.vue | Authorized file display. |
| resources/js/pages/CutiAkademik/components/CutiAkademikStatusBadge.vue | Status/stage representation. |
| resources/js/pages/CutiAkademik/ts/cutiAkademik.constants.ts | Labels and display constants. |
| resources/js/pages/CutiAkademik/ts/cutiAkademik.types.ts | Typed record/page contracts. |
| resources/js/pages/CutiAkademik/ts/cutiAkademik.capabilities.ts | Presentation-only action capabilities. |
| tests/Feature/CutiAkademikTest.php | End-to-end feature and authorization coverage. |
| docs/modules/cuti-akademik.md | Implemented-service documentation. |
| docs/PRD/004_CUTI_AKADEMIK_MODULE.md | This PRD. |

### Modified files

| File | Change |
| --- | --- |
| routes/web.php | Register authenticated Cuti routes and broad role guards. |
| app/Providers/AppServiceProvider.php or policy-registration location | Register Letter policy if discovery is unavailable. |
| database/seeders/DatabaseSeeder.php | Run the Cuti Letter Type seed on fresh databases. |
| resources/js/components/AppSidebarMenu.ts | Add Akademik → Cuti Akademik to relevant navigation. |
| docs/README.md | Link/describe the active service. |
| docs/architecture.md | Document module, attachment, and PDF boundaries. |
| docs/data-model.md | Document Cuti metadata and lifecycle use of shared tables. |
| docs/auth-authorization.md | Document ownership, stage, programme, and file rules. |
| docs/backend-development-base.md | Record shared Letter attachment-service boundary if needed. |
| docs/frontend-development-base.md | Record shared upload-section boundary if needed. |
| docs/modules/letter-services.md | Mark Cuti implemented; retain the other five as planned. |

## Approved implementation tasks

### Task 1 — Activate the Cuti Letter Type

Create the idempotent seed, data migration, and fresh-seed registration with the
approved code, role chain, notes, category, and PDF-template key.

Acceptance criteria:

- Existing databases receive one valid CUTI_AKADEMIK type through migration.
- Fresh seeded databases receive the same configuration.
- Re-running the seed cannot duplicate the type.

Verification:

- php artisan migrate:fresh --seed --env=testing
- Focused code, chain, applicant-role, and notes assertions.

### Task 2 — Build backend service, policies, Requests, and routes

Create the controller, Cuti Form Requests, Letter policy, service, and route
group. Keep every query, snapshot, transition, and stage check in
CutiAkademikService.

Acceptance criteria:

- Controllers delegate to Requests/services.
- Ownership, Letter Type, current stage, and PRODI programme checks hold for
  direct URLs as well as menu flows.
- A request can only follow the approved chain or become REJECTED.

Verification:

- php artisan test tests/Feature/CutiAkademikTest.php
- Denied-access checks as another student and wrong-stage reviewer.

### Task 3 — Add reusable private attachment handling

Implement LetterAttachmentService and LetterAttachmentUpload. Server validation
owns counts, type/size, secure paths, metadata, and cleanup.

Acceptance criteria:

- Submission requires one to five valid documents.
- Bypassing browser checks does not bypass server validation.
- No guessed path, other attachment ID, or different Letter UUID can expose a
  private file.
- Form.vue uses the generic shared section without receiving storage logic.

Verification:

- Focused invalid-count/type/size and cross-user streaming tests.
- pnpm exec eslint resources/js/components/letters/LetterAttachmentUpload.vue

### Task 4 — Implement lifecycle and final PDF

Implement draft save/update/delete, submit, strict stage forwarding/rejection,
and locked Resepsionis finalisation. Add the Cuti PDF generator and Blade view.

Acceptance criteria:

- Submitted records enter PRODI with route/log history and become non-editable.
- Wrong-role, skipped-stage, and stale-state decisions fail safely.
- Rejection preserves evidence/history.
- Resepsionis alone finalizes after DEKAN with a unique manual number and
  DEKAN signatory.
- The PDF is private and a PDF/storage failure leaves the Letter unarchived and
  unnumbered.

Verification:

- Lifecycle, duplicate-number, signatory, file access, race/failure-cleanup
  feature tests.
- Render/open a generated test PDF manually.

### Task 5 — Implement the Vue module and sidebar

Build five Cuti pages, local table, components/types/capabilities, and
navigation using the documented module structure.

Acceptance criteria:

- List, filters, pagination, statuses/stages, and allowed actions work via
  Inertia with server authorization unchanged.
- The form has server-built biodata, approved metadata, upload guidance, and
  useful validation messages.
- Show/Ajuan show clean details/history/files. Ajuan uses the responsive
  two-section decision layout.
- Akademik → Cuti Akademik appears only for applicable users and existing
  navigation remains.
- Narrow layouts remain usable; table overflow and xl review stacking follow
  the frontend base.

Verification:

- pnpm exec eslint resources/js/pages/CutiAkademik resources/js/components/letters/LetterAttachmentUpload.vue
- pnpm build
- Browser checks as MAHASISWA, PRODI, AKADEMIK, KTU, DEKAN, and RESEPSIONIS.

### Task 6 — Update documentation and complete regression verification

Document only implemented behavior, marking the remaining five services
planned.

Verification:

- php artisan test tests/Feature/CutiAkademikTest.php
- php artisan test
- php artisan migrate:fresh --seed --env=testing
- pnpm build
- git diff --check

## Requirement coverage

| Requirement | Tasks | Verification |
| --- | --- | --- |
| Activate only Cuti from the six planned services | 1 | Type seed/migration assertions |
| Snapshot identity and collect approved metadata | 2, 5 | Feature/browser form checks |
| Mandatory secure attachments | 3, 5 | Validation/authorization tests |
| Fixed immediate-stage chain/rejection history | 2, 4, 5 | Workflow tests |
| Manual final number, DEKAN signer, private PDF | 4, 5 | Finalisation/PDF tests |
| Consistent responsive module/sidebar | 5 | Build/browser checks |
| Reusable attachment boundary/documentation | 3, 6 | Code/documentation review |
| Preserve Sidang Nol and Template Surat | 1–6 | Regression review |

## Edge cases

| Scenario | Expected behavior |
| --- | --- |
| No document, more than five documents, invalid type, or over 100 KB | Server rejects with field-level validation errors. |
| Profile changes after creation | Letter retains server-built historical applicant snapshot. |
| Student guesses another Letter UUID | Policy prevents data/file disclosure. |
| Reviewer opens another stage | No decision action is allowed. |
| PRODI is for another programme | Record is unavailable for review. |
| Client supplies the next stage | Service derives the only next chain stage and rejects a mismatch. |
| Duplicate manual number | Database uniqueness rejects finalisation without archive. |
| Concurrent finalisation | Row lock/re-check permits only one successful archive. |
| PDF render/storage fails | No archive/number; safe error state remains and only a new orphan file is cleaned. |
| Attachment/final PDF disappears from storage | Authorized request receives a safe not-found response. |
| Chain/notes are changed in later approved work | Existing snapshot and history remain immutable; this PRD adds no editor. |

## Risks and mitigations

| Risk | Mitigation |
| --- | --- |
| Frontend-only role protection | Enforce ownership, type, stage, programme, and state in policy/service. |
| Private-file leak | Stream only attachment IDs nested under authorized Letters; never accept raw paths. |
| Skipped approvals | Derive next stage only from the seeded ordered chain. |
| Duplicate numbers | Retain shared unique index and lock Letter before finalisation. |
| PDF state inconsistency | Store PDF before archive persistence; clean only the new orphan file on failure. |
| Over-general upload component | Restrict it to presentation and selected-file validation; retain Cuti workflow in Form.vue/service. |
| Sidang Nol regression | Add only new paths/components/services; do not change Sidang Nol schema/routes/files. |

## Final verification

- [x] Existing and fresh databases have exactly one active CUTI_AKADEMIK type
  with the approved notes and approval chain.
- [x] Server snapshots biodata; only approved Cuti metadata is editable.
- [x] Evidence is mandatory, limited, private, and Letter-authorized.
- [x] Each stage forwards only to its immediate chain successor or rejects.
- [x] Only RESEPSIONIS finalizes after DEKAN using a unique number and DEKAN
  signatory.
- [x] Final PDFs are private and generation failure never archives the Letter.
- [x] Pages, table, review layout, and sidebar meet the documented responsive
  UI convention.
- [x] Cuti feature tests pass.
- [x] Existing unrelated test failures are documented rather than
  attributed to this work.
- [x] Fresh migration/seed, production frontend build, and diff check pass.
- [x] Berkas Sidang Nol and Template Surat retain existing behavior.

## Completion record

Implemented on 24 October 2026.

- `php artisan test tests/Feature/CutiAkademikTest.php tests/Feature/LetterFoundationTest.php` passes with 10 tests and 50 assertions.
- `php artisan migrate:fresh --seed --env=testing` passes, including the Cuti Letter Type data migration and idempotent fresh seed.
- `pnpm build`, targeted ESLint, targeted Pint, and `git diff --check` pass.
- The full Laravel suite retains 12 pre-existing authentication/settings failures caused by its stock-test assumptions conflicting with this application's custom NIM authentication, names, and password defaults. The Cuti change also updated Letter Foundation expectations from “no active types” to the approved active `CUTI_AKADEMIK` type.
