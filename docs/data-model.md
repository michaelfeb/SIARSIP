# Data model

This page documents the tables and relations that support the retained application scope. Migrations are the schema source of truth; models express the relations used by the application.

## Identity and roles

```text
roles (1) ──< users (many)
users (1) ──< berkas_sidang_nol (many)
users (1) ──< notes (many)
```

| Table | Important columns | Notes |
| --- | --- | --- |
| `roles` | `id`, `nama` | The seeded numeric IDs are application behavior; see [authorization](auth-authorization.md). |
| `users` | `nama`, `nim`, `email`, `password`, `role_id`, `program_studi`, `nomor_telpon`, `waktu_email_terverifikasi` | `role_id` is nullable and references `roles`, with `null` on role deletion. `program_studi` is currently an integer code. |
| `notes` | `user_id`, `berkas_id`, `jenis_berkas`, `pesan` | A shared notes table. `jenis_berkas = 2` represents Sidang Nol. Legacy Persuratan notes (`1`) are removed during retirement. |

Current programme-of-study codes are `1` Matematika, `2` Kimia, `3` Biologi, `4` Fisika, `5` Farmasi, `6` Ilmu Komputer, `7` Statistika, and `8` Profesi Apoteker.

## Berkas Sidang Nol

```text
users (1) ──< berkas_sidang_nol (many)
berkas_sidang_nol (1) ──< notes (many, where jenis_berkas = 2)
```

| Table | Important columns | Notes |
| --- | --- | --- |
| `berkas_sidang_nol` | `user_id`, six document-path fields, `status`, `nomor_surat`, `tanggal_dikirim`, `surat_balasan`, `tanggal_selesai` | `user_id` cascades on deletion. Document paths point at the private `secure` disk. |
| `notes` | `berkas_id`, `jenis_berkas`, `user_id`, `catatan` | Notes on this workflow are filtered to `jenis_berkas = 2`. |

`status` is an integer with these application values:

| Value | Meaning |
| ---: | --- |
| 0 | Draft |
| 1 | Menunggu (submitted / awaiting decision) |
| 2 | Diterima (accepted) |
| 3 | Ditolak (rejected) |

All original document-path columns are nullable in the final schema, even though the submission controller requires most files when saving a request.

## Template Surat

| Table | Important columns | Notes |
| --- | --- | --- |
| `template_surat` | `nama`, `deskripsi`, `status`, `dokumen_path`, `tanggal_publish` | Standalone downloadable templates. Published templates are shown on the landing page; files are stored publicly. |

Template `status` is treated as a boolean. A value of `1` means published/visible to students and the landing page.

## Reusable Letter foundation

```text
letter_types (1) ──< letters (many)
users (1) ──< letters (many, applicant_id nullable)
letters (1) ──< letter_attachments / letter_routes / letter_logs (many)
users (1) ──< attachment / route / log actors (nullable)
```

| Table | Important columns | Notes |
| --- | --- | --- |
| `letter_types` | `code`, `category`, `name`, `notes_markdown`, `approval_chain`, `applicant_roles`, `pdf_template` | `CUTI_AKADEMIK` is the first active configuration; `code` is unique. |
| `letters` | UUID `id`, `type_id`, applicant snapshots, `status`, `current_stage`, `letter_number`, `meta`, `final_pdf_path` | Shared transactions. Cuti uses DRAFT, IN_PROGRESS, REJECTED, and ARCHIVED. `type_id` restricts deletion; non-null `letter_number` is unique. |
| `letter_attachments` | `letter_id`, `file_path`, `original_name`, `mime`, `size_bytes`, `uploaded_by` | Private Cuti evidence metadata. Physical files use opaque secure-disk paths and authorized record routes. |
| `letter_routes` | `letter_id`, `from_stage`, `to_stage`, `decided_by`, `note` | Future approval-stage history. |
| `letter_logs` | `letter_id`, `actor_id`, `action`, `meta` | Future business audit history. |

For Cuti Akademik, `meta` contains `semester`, `tahun_akademik`, `nomor_hp`,
`nominal_ukt`, `alasan_cuti`, and the applicant programme code snapshot. Its
type configuration defines the fixed `PRODI → AKADEMIK → KTU → DEKAN →
RESEPSIONIS` chain. At least one and at most five private PDF/JPG/JPEG/PNG
attachments are permitted, each at most 100 KB.

Deleting a Letter cascades to its child metadata records. Deleting a user
keeps historical Letter snapshots and changes nullable actor/applicant foreign
keys to `NULL`. The future services own workflow transitions, file storage,
PDF generation, and authorization.

## Landing-page content

| Table | Important columns | Notes |
| --- | --- | --- |
| `carousel` | `nama`, `gambar`, `tanggal_publish`, `status` | `gambar` is a public-storage image path. `status` defaults to active. |
| `link_landing_page` | `nama`, `deskripsi`, `no_urut`, `status`, `link`, `icon`, `color` | Active links are ordered by `no_urut` ascending. `icon` and `color` are optional presentation metadata. |

The landing page only fetches active carousel items, active links, and active templates. Carousel items are ordered by newest publish date; links use their explicit ordering number.

## Data-change guidance

- Use a new migration for every deployed schema change.
- Do not repurpose integer status values without changing the controller, Vue labels, exports, and this document together.
- Keep file paths as relative disk paths; do not persist absolute local paths or public URLs.
- If `notes` is extended to another module, allocate and document a new `jenis_berkas` value before writing records.
- `berkas_persuratan` and `jenis_surat` are retired tables. Their old private attachment files are intentionally retained without database records.
