# Berkas Sidang Nol

## Purpose

Berkas Sidang Nol is the student submission and academic decision workflow for Sidang Nol documents. It is retained as a private-document module: submitted files and generated response letters live on the `secure` storage disk.

## Access and routes

| Capability | Route(s) | Guard |
| --- | --- | --- |
| List submissions | `GET /berkas-sidang-nol` | `auth`, roles `1,6,8` |
| Create/edit/view a submission | `/berkas-sidang-nol/create`, `/{id}/edit`, `/{id}` | `auth`, roles `1,6,8` |
| Save, send, reset, download reply | POST/PUT action routes under `/berkas-sidang-nol` | `auth`, roles `1,6,8` |
| Review queue and decision | `/berkas-sidang-nol/ajuan`, `/{id}/keputusan` | `auth`, roles `6,8` |
| Export Excel | `/berkas-sidang-nol/export` | `auth`, roles `6,8` |
| List uploaded-file URLs / download generated reply | `GET /berkas-sidang-nol/get-uploads/{id}`, `/{id}/download-surat` | `auth`, roles `1,6,8` |
| Preview a private uploaded file (staff route) | `GET /api/berkas-sidang-nol/{path}` | `auth`, roles `6,8` |
| Render an uploaded file from a query path | `GET /berkas-sidang-nol/download-upload?path=...` | `auth` |

The controller also filters the index: a student sees only their own records; staff see records that have reached status `1` or later. Do not rely on that list filter as a substitute for per-record authorization when adding routes.

## Submission contents

The workflow stores paths for six supporting documents:

1. `dokumen_hasil_studi`
2. `dokumen_data_diri`
3. `dokumen_pddikti_ukt`
4. `dokumen_ruangbaca_laboratorium_pkkmb_skpi`
5. `dokumen_office_toefl`
6. `dokumen_tambahan` (optional)

On save, the first five files must be PDFs no larger than 1 MB each; the additional file is optional and follows the same PDF/size rule when provided. Files are normalized into a directory similar to `berkas_sidang_nol/{nim}_{record-id}/` on the `secure` disk.

Other validated fields include the student, letter number, sent date, completion date, and status. The UI should keep the submission form aligned with the controller validation, rather than treating database nullability as proof that a file is optional.

## Workflow

```text
Draft (0)
  └─ student sends → Menunggu (1)
                         ├─ Akademik/Super Admin accepts → Diterima (2)
                         └─ Akademik/Super Admin rejects → Ditolak (3)
                                                            └─ reset → Draft (0)
```

- A draft is created or updated with the student documents.
- `submit` changes a draft from `0` to `1`.
- Staff review status `1` submissions in the ajuan queue.
- A decision records the completion date and may add a note.
- Acceptance formats the letter number as `SN-{number}/UN8.1.28/DV.01/{year}`, renders a DomPDF response letter, and stores it as `surat_balasan.pdf` on the secure disk.
- Rejection records the completion date without generating an acceptance letter.
- Reset is available for rejected records and moves status `3` back to `0`.
- Deleting an accepted record is restricted in controller logic to Super Admin (role `8`); its secure directory is removed with the record.

## Search, ordering, and export

The index supports search by student name/NIM, letter number, and programme-of-study. Staff records prioritize waiting submissions, then order by sent date descending. The Excel export is available to roles `6` and `8` and supports year, status, and programme filters.

## Frontend reference implementation

Berkas Sidang Nol is the visual and structural reference for future retained letter modules. The current Vue implementation lives in `resources/js/pages/BerkasSidangNol/` and follows these boundaries:

```text
BerkasSidangNol/
├── components/       # repeated feature-only UI
├── ts/               # types, constants, composables, pure helpers
├── Ajuan.vue         # staff decision page
├── DataTable.vue     # local list filters, columns, cells, and actions
├── Form.vue          # create/edit orchestration
├── Index.vue         # list page coordinator and export modal
└── Show.vue          # clean detail and direct document viewers
```

`Index.vue` owns Inertia visits, page-level actions, the export modal, and confirmations. Its local `DataTable.vue` owns search, status filtering, headers, cell slots, badges, and action controls. The shared `resources/js/components/data-table/DataTable.vue` owns generic table rendering, responsive overflow, loading/empty state, and pagination only.

The table's `Aksi` column is the only fixed-width column (`230`). Its visible actions come from `sidangNolCapabilities()` and are icon-only outline Buttons with `size-8`, `rounded-lg`, a semantic action color, `title`, and `aria-label`. The shared table uses the same muted-orange primary token for the active pagination page.

The Form uses shared inputs/selects and renders six document uploaders in a responsive two-column grid (`md:grid-cols-2`), producing three rows on desktop. The Show page uses clean stacked details followed by direct embedded PDF cards in the same two-column document layout; it does not require a user to open a preview modal.

The Ajuan page is a one-step staff decision layout. At `xl` widths, it gives Data Permohonan roughly two-thirds of the page and a sticky Keputusan panel one-third. The left side displays clean request details and the direct PDF document grid. The right side holds only Nomor Surat, Penandatangan, Catatan, and the Batal / Tolak / Terima & Generate Surat actions. It preserves the existing decision endpoint and payload.

For the full reusable frontend convention—including naming, shared controls, responsive rules, and component ownership—see [Frontend development base](../frontend-development-base.md).

## Current access caveat

The generic `download-upload` endpoint requires an authenticated session but does not itself check role, record ownership, or constrain the supplied path to a Sidang Nol record. The staff-only API endpoint is the intended protected preview route. Before expanding this module, replace the generic path endpoint with record-based authorization (or enforce equivalent ownership checks) and update this document.

## Notes and decisions

Decision notes are stored in the shared `notes` table with `berkas_id` set to the Sidang Nol record and `jenis_berkas = 2`. The note's user is the staff member creating it. Keep that discriminator on any query or relation so notes from other future modules are not mixed in.

## Implementation touchpoints

- Controller: `app/Http/Controllers/BerkasSidangNolController.php`
- Model: `app/Models/BerkasSidangNol.php`
- Pages: `resources/js/pages/BerkasSidangNol/`
- Export: `app/Exports/BerkasSidangNolExport.php`
- Generated PDF view: `resources/views/surat/sidang-nol.blade.php`
- Routes: `routes/web.php`
