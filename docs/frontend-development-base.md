# Frontend development base

This is the frontend convention for the SIARSIP revamp. **Berkas Sidang Nol** is the reference module: new modules should use the same page boundary, support folders, and explicit domain naming.

The convention is designed for the current Vue 3 + Inertia application. It does not introduce a second routing or state-management system.

## Core rule

Keep Inertia pages at the root of a module folder. A module may also keep one local `DataTable.vue` at its root when it is the page-specific list coordinator. Put all other module-only components, TypeScript code, and tests in dedicated child folders.

```text
resources/js/pages/BerkasSidangNol/
├── components/
│   ├── SidangNolDetails.vue
│   ├── SidangNolDocumentPreview.vue
│   └── SidangNolDocumentUpload.vue
├── ts/
│   ├── constants.ts
│   ├── pdf.ts
│   ├── types.ts
│   ├── filepond-plugin-pdf-preview.d.ts
│   └── useSidangNolDocuments.ts
├── __testing__/
│   ├── BerkasSidangNolIndex.spec.ts
│   └── useBerkasSidangNolDocuments.spec.ts
├── Ajuan.vue
├── DataTable.vue
├── Form.vue
├── Index.vue
└── Show.vue
```

`__testing__/` is created when the first test for the module is added. Git does not keep empty folders, so its absence in a module without tests is expected.

## Folder responsibilities

| Location | Contains | Does not contain |
| --- | --- | --- |
| module root | Inertia page entries: `Index`, `Form`, `Show`, and module-specific page states such as `Ajuan`; plus the one local `DataTable.vue` when needed | reusable visual fragments, shared types, or composables |
| `components/` | Vue components used only by this module | full Inertia pages or application-wide UI primitives |
| `ts/` | types, constants, composables, helper functions, and third-party declaration files | Vue page components or unrelated global utilities |
| `__testing__/` | module unit/component tests and fixtures | production code |

Promote a component from `BerkasSidangNol/components/` to `resources/js/components/` only when it is genuinely shared by more than one module and its API is stable. Do not promote a component merely because it is large.

## Page responsibilities

| Page | Responsibility |
| --- | --- |
| `Index.vue` | List, filters, table interaction, pagination, and navigation to the module's other pages. |
| `Form.vue` | Create and edit form orchestration, including validation feedback and document inputs. |
| `Show.vue` | Read-only record detail, document visibility, and allowed user actions. |
| `Ajuan.vue` | Staff/admin review or decision workflow for a submission. |

A page may compose module components and call module composables. It must not redefine a shared data type. Keep a page-owned layout section inline while its composition is specific to that page; extract a large, stable visual fragment into `components/` once it has a clear module-level responsibility.

Keep requests in the existing Inertia style: use page props for server data, `useForm` for form submissions, and Inertia navigation/partial reloads for page changes. Frontend code never queries the database directly; the Laravel controller and service remain the server boundary.

## Shared data-table conventions

Use `resources/js/components/data-table/DataTable.vue` as the visual base for new server-driven list pages. The feature's local `DataTable.vue` owns its headers, filters, cell slots, and row actions; the shared wrapper owns the table frame, responsive overflow, row dividers, loading state, and pagination controls.

Let content columns size naturally. Add a fixed `width` only where a control requires predictable space—normally the `Aksi` column, because it contains a known set of icon buttons. Do not assign widths to every data column merely to fill the table; that makes the generated `No` index column absorb remaining space and harms responsiveness.

Keep all horizontal dividers on the same `--border` token in the shared wrapper. A feature must not add darker per-row borders. For select-like filters, use the local shadcn-style components from `@/components/ui/select`; this provides the project's consistent trigger, keyboard behavior, and chevron rather than browser-native select styling.

### Table ownership and action column

Keep the list responsibilities split exactly as they are in Berkas Sidang Nol:

```text
Index.vue
├── page heading, top actions, export modal, navigation, Inertia requests
└── DataTable.vue
    ├── search and filter state
    ├── feature headers, badges, cells, and Aksi controls
    └── emitted user intents
        └── components/data-table/DataTable.vue
            └── reusable table shell, overflow, loading state, and pagination
```

The local table emits intents such as `view`, `edit`, `delete`, `submit`, `reset`, `review`, and `download-reply`; the page performs routing, confirmation, or mutation. Do not put route names, SweetAlert calls, or Inertia navigation into the shared table wrapper.

The `Aksi` header is the only fixed-width column by default. Use a width that fits the complete known action set (Sidang Nol uses `230`), while all data columns retain natural width. Every action control is a shared `Button` with `variant="outline"`, `size="icon"`, `size-8`, and `rounded-lg`, with both `title` and `aria-label`. Use semantic color pairs consistently:

| Intent | Icon | Color family |
| --- | --- | --- |
| view | `Eye` | blue |
| review | `Check` | emerald |
| submit | `Send` | blue |
| reset | `RotateCcw` | sky |
| edit | `Pencil` | amber |
| delete | `Trash2` | rose |
| download reply | `Download` | emerald |

The local table decides which actions are visible from the feature capability helper. This is presentation logic only: Laravel remains the authorization authority.

### Index filters and export

Keep search and module filters inside the local `DataTable.vue`, not in `Index.vue`. Debounce text search, reset the query to page one after a changed filter, and emit the full server-query object. Use shared `Input` and `Select` controls for both table filters and the Index export modal. Export filters remain page-owned because the export modal and its download action belong to `Index.vue`.

## Letter-module page layouts

Future letter modules should follow the Berkas Sidang Nol page shapes unless their workflow requires a documented exception. Reuse the layout and interaction pattern; replace only the feature data, labels, document definitions, capability helper, and route names.

### Form — create and edit

- Use shared `Input`, `Label`, `Select`, and `Button` controls.
- Keep page-level form state and Inertia `useForm` submission in `Form.vue`; place each repeated document uploader in a module component.
- Render document uploaders with `grid gap-x-6 gap-y-8 md:grid-cols-2`. Six documents therefore form two columns and three rows on desktop, and one column on mobile.
- A remote choice such as Mahasiswa remains a shared Select with its search input inside `SelectContent`; the page owns debounce, loading, and option state.
- Keep secondary and primary form actions responsive with `flex-col-reverse` on mobile and a row layout from `sm` upward.

### Show — read-only record and documents

- Render record information with `SidangNolDetails` using the `clean` variant: a readable stacked definition list, not a bordered data table.
- Render each uploaded PDF directly below the detail section in `grid gap-6 md:grid-cols-2`. Every document card owns its label, loading state, unavailable state, and scrollable embedded PDF.
- Use `sidangNolPdfEmbedUrl()` for an embedded document URL. It requests a toolbar-free view (`toolbar=0`, `navpanes=0`, and `scrollbar=0`) in viewers that support standard PDF fragment parameters. Do not make document preview modals the default read-only experience.

### Ajuan — staff decision

`Ajuan.vue` is a one-step review page, not a detail page with a decision modal.

```text
desktop (xl and wider)
├── left: minmax(0, 2fr)
│   ├── Data Permohonan: clean details
│   └── Dokumen Permohonan: direct PDF cards, 2 columns × 3 rows
└── right: minmax(20rem, 1fr)
    └── sticky Keputusan form
        ├── Nomor Surat
        ├── shared Penandatangan Select
        ├── optional Catatan
        └── Batal, Tolak, Terima & Generate Surat
```

Use `xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)]` for the two-column proportion and `xl:sticky xl:top-6` for the decision panel. Below `xl`, stack the request content before the decision form. Keep decision behavior in the page: set the status, submit the existing `useForm`, show existing success/error feedback, and navigate only after the result.

### Component and support-file separation

| Responsibility | Location | Sidang Nol reference |
| --- | --- | --- |
| Inertia page orchestration | module root | `Index.vue`, `Form.vue`, `Show.vue`, `Ajuan.vue` |
| Feature list UI | module root | `DataTable.vue` |
| Repeated document/detail visual fragments | `components/` | `SidangNolDocumentUpload.vue`, `SidangNolDetails.vue` |
| Document URL state and network request | `ts/` composable | `useSidangNolDocuments.ts` |
| Fixed document definitions, statuses, labels, capabilities | `ts/constants.ts` | `SIDANG_NOL_DOCUMENTS`, `sidangNolCapabilities()` |
| Feature types | `ts/types.ts` | `SidangNol`, `SidangNolDocumentKey` |
| Pure PDF embed URL helper | `ts/pdf.ts` | `sidangNolPdfEmbedUrl()` |

Do not copy a whole page into a new letter module. Start with the same boundaries, then create new feature-specific components, constants, types, and helpers where the data differs.

## Canonical naming

Use the full feature name **`BerkasSidangNol`** for new module-specific exported names. This makes imports, browser errors, test output, and future cross-module work unambiguous.

| Concern | Convention | Example |
| --- | --- | --- |
| feature folder | PascalCase domain name | `BerkasSidangNol/` |
| pages | page intent only; context comes from the folder | `Index.vue`, `Form.vue`, `Show.vue`, `Ajuan.vue` |
| module component | feature name + purpose, PascalCase | `BerkasSidangNolDocumentUpload.vue` |
| composable | `use` + feature name + responsibility | `useBerkasSidangNolDocuments.ts` |
| type/interface | feature name + domain concept, PascalCase | `BerkasSidangNolRecord`, `BerkasSidangNolPageProps` |
| constants file | feature name, camelCase filename | `berkasSidangNol.constants.ts` |
| constant value | full feature name in `SCREAMING_SNAKE_CASE` | `BERKAS_SIDANG_NOL_DOCUMENTS` |
| test file | subject name + test suffix | `useBerkasSidangNolDocuments.spec.ts` |

Use a descriptive verb followed by the full domain object for exported functions. Examples:

```ts
loadBerkasSidangNolDocuments()
getBerkasSidangNolDocumentCapabilities()
submitBerkasSidangNolApplication()
formatBerkasSidangNolSubmissionDate()
openBerkasSidangNolDocumentPreview()
```

Avoid vague exported names such as `loadData`, `process`, or `utils`. Event handlers follow the separate `on…` convention below; module-level and exported functions must retain the full feature context.

## Button styling

Use the shared `Button` from `@/components/ui/button` for application actions. Its default (primary) variant uses the muted orange `#c2410c` with `#9a3412` on hover. Every button size uses `rounded-lg`. Do not add a different corner-radius utility to a `Button` unless a documented component-specific exception is needed.

Use `variant="outline"` for secondary actions and explicit semantic colors only for actions such as destructive, approve, or download. Data-table icon actions also use `rounded-lg`; pagination controls follow the same radius and use `#c2410c` for the active page.

## Input styling

Use shared `Input` and `Select` components for text inputs and selects. Their base controls use `rounded-lg`; provide only sizing, spacing, and state classes at the call site, not another corner-radius class. Native controls that cannot yet use a shared component must also use `rounded-lg`.

## Template and button handler naming

For an interaction initiated by a template event—such as a button click, form submission, input change, or emitted child event—name the page or component function with the **`on`** prefix and an action in PascalCase.

| Template event | Handler name | Typical parameters |
| --- | --- | --- |
| form submit | `onSubmit()` | form values only when they are not already held by `useForm` |
| button click to create | `onCreate()` | optional initial data |
| button click to update | `onUpdate(id)` | record ID or record |
| button click to delete | `onDelete(id)` | record ID or record |
| export modal open/close | `onOpenExportModal()` / `onCloseExportModal()` | none |
| open a document preview | `onOpenDocumentPreview(document)` | the selected document |
| close a dialog/preview | `onCloseDocumentPreview()` | none unless the event supplies useful data |
| input or select change | `onStatusChange(status)` | the changed value, not the raw event where possible |
| choose a signatory | `onSignerChange(value)` | selected signatory value |
| accept or reject a request | `onSubmitDecision(status)` | the selected decision status |
| cancel a page workflow | `onCancel()` | none |
| child component event | `onDocumentUploaded(document)` | the payload emitted by the child |

```vue
<template>
    <form @submit.prevent="onSubmit">
        <button type="submit">Simpan</button>
    </form>

    <button type="button" @click="onOpenDocumentPreview(document)">
        Lihat dokumen
    </button>
</template>

<script setup lang="ts">
function onSubmit(): void {
    // submit the existing useForm state
}

function onOpenDocumentPreview(document: BerkasSidangNolDocument): void {
    // set the local preview state
}
</script>
```

Use `onSubmit`, not `submitForm`, `handleSubmit`, `processSubmit`, or an anonymous function in the template. The handler describes the UI event; it may call an explicit domain/composable function such as `submitBerkasSidangNolApplication()` internally.

Keep event handlers private to their page or component unless another module needs the behavior. If it becomes shared domain behavior, move it to `ts/` and use the full feature-specific exported name instead of the `on…` name.

## TypeScript convention

All non-SFC frontend support code for the module belongs in `ts/`; do not create a separate `js/` folder. New Vue pages and components should use TypeScript when their code changes materially.

```ts
// ts/berkasSidangNol.types.ts
export interface BerkasSidangNolRecord {
    id: number
    status: string
}

// ts/useBerkasSidangNolDocuments.ts
export function useBerkasSidangNolDocuments() {
    // module document state and domain helpers
}
```

Keep plain values and immutable lookups in `berkasSidangNol.constants.ts`. Keep reusable reactive state and behavior in `useBerkasSidangNol*.ts`. Do not place an API call, page-only local state, and unrelated formatting functions in one catch-all file.

## Tests

When a frontend test runner is added or expanded, place Berkas Sidang Nol tests inside its local `__testing__/` folder. Name tests after the page, component, or composable under test and keep fixtures local until another module needs them.

```text
__testing__/
├── BerkasSidangNolDocumentUpload.spec.ts
├── BerkasSidangNolShow.spec.ts
└── useBerkasSidangNolDocuments.spec.ts
```

Test the observable module contract: conditional document visibility, form payloads, status-specific actions, emitted component events, and composable behavior. Do not test private implementation details such as a particular local ref name.

## Applying the reference to another module

For example, Template Surat follows the same shape while preserving its own domain name:

```text
resources/js/pages/TemplateSurat/
├── components/
│   └── TemplateSuratEditor.vue
├── ts/
│   ├── templateSurat.types.ts
│   └── useTemplateSuratEditor.ts
├── __testing__/
├── Form.vue
├── Index.vue
└── Show.vue
```

The structure is consistent, but the folder must not be filled speculatively. Add a component, composable, or test folder only when that responsibility exists.

## Shared Letter attachment section

`resources/js/components/letters/LetterAttachmentUpload.vue` is the shared
file-selection section for Letter modules. It owns generic client-side selected
file display and limit feedback through `v-model`. It must not own a route,
storage path, Letter Type, request state, or module label. The owning form
still owns submission and server validation, which remains authoritative.

## Shared Letter approval flow

`resources/js/components/letters/LetterApprovalFlow.vue` displays a Letter
Type's configured `approval_chain` without owning workflow state. Pass the
server-provided stages through `approval-chain`; it maps known roles to their
reader-friendly labels and remains usable for future configured roles.

The guidance title and the approval information are intentional props, so a
letter can adjust its copy without creating another shared component:

```vue
<LetterApprovalFlow
    :approval-chain="formContext.approvalChain"
    title="Alur Persetujuan Surat"
    information-title="Informasi Proses Persetujuan"
    information-description="Permohonan akan ditinjau secara bertingkat."
    estimate-label="Estimasi waktu"
    estimate="3–5 hari kerja"
/>
```

The create/edit controller must obtain the guide source (`notes_markdown`) and
approval chain through its module Service. The Service renders the trusted
server-managed markdown before passing it as page data; the form only presents
that content and must not duplicate it in frontend constants.

## Shared Letter badges

Letter status and approval-stage presentation live in
`resources/js/components/letters/Badge.vue`. The component has two modes:
`type="status"` renders a Letter lifecycle (`DRAFT`, `IN_PROGRESS`,
`REJECTED`, or `ARCHIVED`), while `type="stage"` renders an active approval
stage. Stage mode maps PRODI, AKADEMIK, KTU, DEKAN, WD/WD1/WD2/WD3,
RESEPSIONIS, and RUANG BACA to compact labeled badges. Both modes have a
neutral fallback for future configured values and render `—` when no value is
active.

```vue
<Badge type="status" :value="letter.status" />
<Badge type="stage" :value="letter.current_stage" />
```

Keep **Status** and **Stage** as separate columns in Letter data tables:
status communicates lifecycle, while the stage badge communicates the current
approver. Do not
combine the stage into a status badge or replace it with a text line below the
status in a dense table.

## Existing Sidang Nol names

The current Berkas Sidang Nol refactor already follows the page-root, `components/`, and `ts/` boundary. Some supporting files still use the older concise `SidangNol*` names, such as `SidangNolDetails.vue` and `useSidangNolDocuments.ts`.

Do not rename those files solely to create a noisy change. When a file is next substantially revised, rename it and its imports to the canonical `BerkasSidangNol*` convention in the same focused change. All newly created module-specific files should use the canonical form immediately.

## Review checklist

Before considering a frontend module change complete, confirm:

- Only actual pages are at the module root.
- Module-only components are under `components/`.
- Types, constants, composables, and declarations are under `ts/`.
- New exported names clearly identify `BerkasSidangNol` (or the relevant feature).
- Reusable UI is not promoted globally without a second real consumer.
- Tests, when present, live in `__testing__/` and describe visible behavior.
- The Inertia page name still matches the module path and SFC filename exactly, including case.
