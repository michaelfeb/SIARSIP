# Dashboard

## Purpose

The Dashboard is the staff-facing summary of submitted letter services. It
currently combines Berkas Sidang Nol and Cuti Akademik without changing either
module's workflow, records, or routes. Students continue to be redirected from
`/dashboard` to Berkas Sidang Nol.

## Filters

The authenticated `GET /dashboard` Inertia page accepts these optional query
filters:

| Filter | Accepted values | Default |
| --- | --- | --- |
| `letter_type` | `all`, `sidang-nol`, `cuti-akademik` | `all` |
| `month` | `1` through `12` | all months |
| `year` | calendar year | all available years |

`DashboardRequest` validates the filters and `DashboardService` owns all
aggregation. The frontend sends filter changes through an Inertia GET visit;
it does not calculate totals locally.

## Status normalization

Drafts are excluded because they have not entered the approval process.

| Dashboard metric | Berkas Sidang Nol | Cuti Akademik |
| --- | --- | --- |
| Total Surat | statuses `1`, `2`, `3` | `IN_PROGRESS`, `ARCHIVED`, `REJECTED` |
| Dalam Proses | `1` / Menunggu | `IN_PROGRESS` |
| Diarsipkan | `2` / Diterima | `ARCHIVED` |
| Ditolak | `3` / Ditolak | `REJECTED` |

Sidang Nol status `2` is an archived outcome because acceptance generates and
stores its final response letter.

## Data sources

- Berkas Sidang Nol uses `berkas_sidang_nol.tanggal_dikirim` for period
  filters and joins `users.program_studi` for the distribution.
- Cuti Akademik uses `letters.submitted_at` for period filters and the saved
  `letters.program_studi` snapshot for the distribution.

The service performs grouped database counts per source, normalizes their
small aggregate results, and returns the combined summary and distribution to
the Dashboard Inertia page.

## Adding a future letter service

Do not add a future service to the dashboard merely because it appears in the
sidebar. After its workflow and archival meaning are approved:

1. Define its submitted, in-progress, archived, and rejected lifecycle states.
2. Add its filtered aggregate query to `DashboardService`.
3. Add its status mapping and a feature test covering every metric.
4. Update this document and `docs/PRD/008_UNIFIED_LETTERS_DASHBOARD.md` if the
   dashboard contract changes.

The Dashboard-only visual fragments remain in
`resources/js/pages/Dashboard/components/`; do not promote them to shared
components until another real page needs the same stable API.
