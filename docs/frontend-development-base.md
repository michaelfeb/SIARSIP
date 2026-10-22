# Frontend development base

This is the frontend convention for the SIARSIP revamp. **Berkas Sidang Nol** is the reference module: new modules should use the same page boundary, support folders, and explicit domain naming.

The convention is designed for the current Vue 3 + Inertia application. It does not introduce a second routing or state-management system.

## Core rule

Keep only Inertia pages at the root of a module folder. Put all module-only components, TypeScript code, and tests in dedicated child folders.

```text
resources/js/pages/BerkasSidangNol/
├── components/
│   ├── BerkasSidangNolDetails.vue
│   ├── BerkasSidangNolDocumentPreview.vue
│   └── BerkasSidangNolDocumentUpload.vue
├── ts/
│   ├── berkasSidangNol.constants.ts
│   ├── berkasSidangNol.types.ts
│   ├── filepond-plugin-pdf-preview.d.ts
│   └── useBerkasSidangNolDocuments.ts
├── __testing__/
│   ├── BerkasSidangNolIndex.spec.ts
│   └── useBerkasSidangNolDocuments.spec.ts
├── Ajuan.vue
├── Form.vue
├── Index.vue
└── Show.vue
```

`__testing__/` is created when the first test for the module is added. Git does not keep empty folders, so its absence in a module without tests is expected.

## Folder responsibilities

| Location | Contains | Does not contain |
| --- | --- | --- |
| module root | Inertia page entry components: `Index`, `Form`, `Show`, and module-specific page states such as `Ajuan` | reusable visual fragments, shared types, or composables |
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

A page may compose module components and call module composables. It should not duplicate a document-preview implementation, redefine a shared data type, or contain a large isolated UI section that can be a component.

Keep requests in the existing Inertia style: use page props for server data, `useForm` for form submissions, and Inertia navigation/partial reloads for page changes. Frontend code never queries the database directly; the Laravel controller and service remain the server boundary.

## Shared data-table conventions

Use `resources/js/components/data-table/DataTable.vue` as the visual base for new server-driven list pages. The feature's local `DataTable.vue` owns its headers, filters, cell slots, and row actions; the shared wrapper owns the table frame, responsive overflow, row dividers, loading state, and pagination controls.

Let content columns size naturally. Add a fixed `width` only where a control requires predictable space—normally the `Aksi` column, because it contains a known set of icon buttons. Do not assign widths to every data column merely to fill the table; that makes the generated `No` index column absorb remaining space and harms responsiveness.

Keep all horizontal dividers on the same `--border` token in the shared wrapper. A feature must not add darker per-row borders. For select-like filters, use the local shadcn-style components from `@/components/ui/select`; this provides the project's consistent trigger, keyboard behavior, and chevron rather than browser-native select styling.

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
| open a document preview | `onOpenDocumentPreview(document)` | the selected document |
| close a dialog/preview | `onCloseDocumentPreview()` | none unless the event supplies useful data |
| input or select change | `onStatusChange(status)` | the changed value, not the raw event where possible |
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
