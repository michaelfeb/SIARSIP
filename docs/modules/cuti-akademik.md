# Cuti Akademik

## Purpose

Cuti Akademik is the first active service on the shared Letters foundation.
Mahasiswa create a request using an authenticated biodata snapshot, optionally
provide supporting evidence, follow its status, and download the final official PDF.

## Form data and evidence

Nama, NIM, and Program Studi are built on the server from the authenticated
account. The student enters Semester, Tahun Akademik, Nomor Kontak, Nominal
UKT, and alasan cuti.

Supporting evidence is optional: zero to five PDF, JPG, JPEG, or PNG files,
no more than 100 KB each. Files are private on the `secure` disk; the browser
accesses them only through an authorized route containing the Letter UUID and
attachment ID.

## Lifecycle

| Status | Meaning |
| --- | --- |
| DRAFT | Student may edit, add documents, delete, or submit. |
| IN_PROGRESS | Waiting at the role in `current_stage`. |
| REJECTED | A current-stage reviewer rejected it and supplied a note. |
| ARCHIVED | Resepsionis generated and stored the final official PDF. |

The fixed approval sequence is:

    PRODI → AKADEMIK → KTU → DEKAN → RESEPSIONIS

Every reviewer may forward only to the immediate next role or reject. PRODI is
limited to the applicant's Program Studi. RESEPSIONIS is the configured
finalization role: it enters a unique official number and generates/archives
the final PDF. DEKAN is separately configured as the signatory role selected
for that PDF.

## Backend ownership

`CutiAkademikController` is the HTTP/Inertia boundary. Its Form Requests own
HTTP validation; `CutiAkademikService` owns Cuti-specific queries, snapshots,
Program Studi review scope, and its PDF template/data configuration. It extends
the abstract `LetterService`, which owns shared Letter Type checks, attachment
access, approval-chain transitions, locks, finalisation, PDF storage, and audit
entries. `PdfGeneratorService` renders the configured Blade view; Cuti provides
`letters.cuti-akademik` and only saved Letter, meta, and signatory snapshot data.
`LetterAttachmentService` is the small shared private-file persistence service.

## SUPER ADMIN authority

SUPER ADMIN can see all Cuti Akademik records, create a draft, manage any
draft, submit it, and record a forward/reject decision at any in-progress
stage. The approval chain remains intact: SUPER ADMIN can finalize only after
the request reaches RESEPSIONIS, the configured finalization stage.

Finalization still requires a selected user with the configured signatory role.
For Cuti Akademik that role is DEKAN, so a SUPER ADMIN account cannot select
itself unless it separately has that role. When SUPER ADMIN edits another
applicant's draft, the original Program Studi snapshot is retained for PRODI
scope checks.

## Frontend ownership

The module follows the application letter-page convention:

```text
CutiAkademik/
├── components/     module-only details and attachment display
├── ts/             types, constants, and presentation capabilities
├── Index.vue       page coordinator
├── DataTable.vue   local filters, cells, and actions
├── Form.vue        draft form
├── Show.vue        clean read-only detail/history
└── Ajuan.vue       reviewer/finalisation decision page
```

The shared `LetterAttachmentUpload.vue` remains generic. The module uses the
same responsive table, rounded controls, action-button semantics, and
two-section Ajuan layout as the Berkas Sidang Nol reference.

Create and edit forms receive a server-owned `formContext` from
`CutiAkademikService`: the Letter Type `notes_markdown` (with its safe rendered
HTML) and `approval_chain`. `Form.vue` presents the guide and composes shared
`LetterApprovalFlow.vue`; it does not copy workflow stages or guide copy into
frontend constants.

Its data table keeps Status and Stage separate: shared `Badge` with
`type="status"` renders the Letter lifecycle, while `type="stage"` renders the
current PRODI, AKADEMIK, KTU, DEKAN, RESEPSIONIS, or other configured approval
stage.
