# Future letter services

## Shared foundation

The `letter_types` and `letters` tables provide a single reusable foundation
for future academic-letter services. A Letter Type is configuration; a Letter
is one request. Type-specific form data belongs in `letters.meta`; repeated
files, routing history, and audit records use the shared child tables.

Cuti Akademik is now the active reference implementation. It adds its own
routes, screens, upload/PDF operations, and fixed chain while using this shared
foundation. It does not replace or modify Berkas Sidang Nol.

## Planned catalogue

| Service | Status | Ownership when implemented |
| --- | --- | --- |
| Cuti Akademik | Implemented | Uses the fixed PRODI → AKADEMIK → KTU → DEKAN → RESEPSIONIS chain, private evidence, and final DomPDF output. |
| Magang | Planned | Defines its own domain fields and workflow while using the shared Letter foundation. |
| Penurunan UKT | Planned | Defines its own domain fields and workflow while using the shared Letter foundation. |
| UKT 50% | Planned | Defines its own domain fields and workflow while using the shared Letter foundation. |
| Pengunduran Diri | Planned | Defines its own domain fields and workflow while using the shared Letter foundation. |
| Bebas Ruang Baca | Planned | Defines its own domain fields and workflow while using the shared Letter foundation. |

Each service receives an idempotent Letter Type seed only after its
configuration is approved. The reference project’s LetterTypesSeeder is not
SIARSIP seed data because its letter catalogue and role vocabulary differ.

## Rules for future services

- Use one `letters` table; do not add a main transaction table per service.
- Build applicant snapshots on the server from the authenticated user and the
  selected Letter Type. The client never owns those values.
- Keep lifecycle `status` separate from the role in `current_stage`.
- Apply approval-chain authorization, transactions, row locks, routes, and
  audit logs in the service implementation.
- Store supporting-file metadata in `letter_attachments`; final official PDFs
  use `letters.final_pdf_path` instead.
- Official numbers are manually entered at the future finalisation step and
  are unique across all Letters.
- Keep Template Surat independent. It is a public downloadable-template
  module, not a Letter Type.

## Required implementation pattern

Cuti Akademik is the reference implementation for every future service.

1. Add one Letter Type Blade template at `resources/views/letters/{letter}.blade.php`.
2. Add `XxxController`, `XxxRequest`, and `XxxService`; do not create an
   `XxxPdfGenerator` class.
3. Make the service extend `LetterService` and configure its unique type code,
   finalization role, signatory role, `pdfView()`, and `pdfData()`.
4. Add an idempotent `XxxLetterTypeSeeder`, then a dedicated migration that
   invokes that seed on production environments.
5. Implement only the Letter Type-specific draft fields, metadata snapshot,
   data table query, and any reviewer scope. Reuse the parent workflow for
   `ajuan()`, `decide()`, and `finalize()`.
6. Add service/feature coverage for draft creation, ownership visibility,
   approval order, finalizer/signatory separation, generated PDF storage, and
   failure cleanup.

The complete method naming and inheritance contract is maintained in
[Backend development base](../backend-development-base.md#letter-module-standard).
