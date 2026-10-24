# Future letter services

## Shared foundation

The `letter_types` and `letters` tables provide a single reusable foundation
for future academic-letter services. A Letter Type is configuration; a Letter
is one request. Type-specific form data belongs in `letters.meta`; repeated
files, routing history, and audit records use the shared child tables.

This foundation has no routes, screens, active Letter Type records, upload
operations, PDF generation, or approval actions yet. It does not replace or
modify Berkas Sidang Nol.

## Planned catalogue

| Service | Status | Ownership when implemented |
| --- | --- | --- |
| Cuti Akademik | Next service | Defines its form, validation, required files, approval chain, Markdown notes, PDF template, and transitions. |
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
