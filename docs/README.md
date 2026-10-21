# SIARSIP documentation

SIARSIP is the **Portal Layanan Surat Akademik FMIPA ULM**. It is a server-rendered Laravel application with a Vue single-page experience delivered through Inertia.

This documentation describes the application as implemented in the repository on 21 October 2026. It distinguishes the retained scope from legacy functionality that remains in the codebase.

## Retained product scope

The next iteration keeps these modules:

1. Authentication and account settings
2. Berkas Sidang Nol
3. Template Surat
4. Carousel
5. Link Landing Page

Other modules and routes, such as Berkas Persuratan, users, jenis surat, and dashboard, still exist in the current codebase. They are recorded where they affect shared architecture or permissions, but are not documented as retained product modules.

## Reading order

- [Architecture and technology](architecture.md) — foundation, request flow, directory layout, and storage.
- [Local development](local-development.md) — prerequisites, environment, database, and build commands.
- [Authentication and authorization](auth-authorization.md) — login, registration, roles, and route guards.
- [Data model](data-model.md) — retained tables, relationships, status values, and enumerations.
- [Backend development base](backend-development-base.md) — the agreed controller, Form Request, and service convention for the revamp.
- [Berkas Sidang Nol](modules/berkas-sidang-nol.md) — complete submission and decision workflow.
- [Template Surat](modules/template-surat.md) — template publishing and download behavior.
- [Carousel](modules/carousel.md) — landing-page announcement image management.
- [Link Landing Page](modules/link-landing-page.md) — landing-page shortcut management.

## Documentation conventions

- **Implemented** means the behavior is present in the current source code.
- **Route guard** means the middleware declared on the route; it is not a statement about every possible authorization check.
- Role names and IDs come from `database/seeders/RoleSeeder.php`. Production data must preserve those IDs or the current authorization rules will change meaning.
- Paths are repository-relative unless stated otherwise.

## Source of truth

When this documentation and code ever disagree, the code is the source of truth. Update the relevant document in the same pull request as any route, model, migration, or workflow change.
