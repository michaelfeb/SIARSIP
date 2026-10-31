# Alur Persetujuan Surat

## Purpose

`Alur Persetujuan Surat` is the settings module for a Letter Type's approval chain and applicant guidance. It updates the existing `letter_types.approval_chain` and `letter_types.notes_markdown` fields; it does not create roles or rewrite historical letters.

## Access and safety

Only AKADEMIK and SUPER ADMIN can access the module. A Letter Type with an `IN_PROGRESS` Letter is locked: its workflow and notes cannot change until active letters are rejected or archived. The backend re-checks this inside a transaction and locks the Letter Type row.

Role names must exist in the role master and appear only once in a chain. When RESEPSIONIS is already the last stage, it is retained as the last stage and cannot be removed or reordered.

## Cuti Akademik

Cuti Akademik must finish at RESEPSIONIS because that role finalizes and archives its official PDF. At finalization, RESEPSIONIS can select any role present in the configured approval chain and then a user with that role. The role immediately before RESEPSIONIS is selected by default. The selected role and person are stored in `letters.meta.signatory` before PDF generation. Archived PDFs and signer snapshots are immutable.
