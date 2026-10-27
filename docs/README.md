# SIARSIP documentation

SIARSIP is the **Portal Layanan Surat Akademik FMIPA ULM**. It is a server-rendered Laravel application with a Vue single-page experience delivered through Inertia.

This documentation describes the application as implemented in the repository on 23 October 2026.

## Retained product scope

The next iteration keeps these modules:

1. Authentication and account settings
2. Berkas Sidang Nol
3. Template Surat
4. Carousel
5. Link Landing Page
6. Cuti Akademik
7. Reusable Letter foundation for future academic-letter services

Berkas Persuratan and the old Jenis Surat module are retired. Their legacy URLs deliberately return HTTP `410 Gone`; their database data is removed while the old private attachment files remain untouched on storage.

## Reading order

- [Architecture and technology](architecture.md) — foundation, request flow, directory layout, and storage.
- [Local development](local-development.md) — prerequisites, environment, database, and build commands.
- [Authentication and authorization](auth-authorization.md) — login, registration, roles, and route guards.
- [Data model](data-model.md) — retained tables, relationships, status values, and enumerations.
- [Backend development base](backend-development-base.md) — the agreed controller, Form Request, and service convention for the revamp.
- [Frontend development base](frontend-development-base.md) — the Berkas Sidang Nol reference layout, naming, and test convention for Vue modules.
- [Berkas Sidang Nol](modules/berkas-sidang-nol.md) — complete submission and decision workflow.
- [Template Surat](modules/template-surat.md) — template publishing and download behavior.
- [Carousel](modules/carousel.md) — landing-page announcement image management.
- [Link Landing Page](modules/link-landing-page.md) — landing-page shortcut management.
- [Cuti Akademik](modules/cuti-akademik.md) — student application, staged review, private evidence, and final letter generation.
- [Letter PDF preview contract](modules/pdf-preview.md) — mandatory draft-preview behavior for every active Letter module.
- [Future letter services](modules/letter-services.md) — shared foundation and the remaining planned services.
- [PRD 002](PRD/002_LEGACY_RETIREMENT_AND_TEMPLATE_SURAT_DECOUPLING.md) — approved legacy-retirement scope.
- [PRD 003](PRD/003_LETTER_TYPES_AND_LETTERS_FOUNDATION.md) — reusable foundation for future letter services.
- [PRD 004](PRD/004_CUTI_AKADEMIK_MODULE.md) — approved Cuti Akademik module scope.

## Documentation conventions

- **Implemented** means the behavior is present in the current source code.
- **Route guard** means the middleware declared on the route; it is not a statement about every possible authorization check.
- Role names and IDs come from `database/seeders/RoleSeeder.php`. Production data must preserve those IDs or the current authorization rules will change meaning.
- Paths are repository-relative unless stated otherwise.

## Source of truth

When this documentation and code ever disagree, the code is the source of truth. Update the relevant document in the same pull request as any route, model, migration, or workflow change.
