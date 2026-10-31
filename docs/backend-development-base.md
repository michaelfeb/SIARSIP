# Backend development base

## Status and purpose

This is the **target development convention for the revamp**. It is not a claim that all existing controllers already follow it.

The goal is a small, maintainable Laravel structure for the retained modules:

- Berkas Sidang Nol
- Template Surat
- Carousel
- Link Landing Page
- Authentication and account settings

It intentionally avoids a Repository layer, large domain framework, and many one-method classes. The application is small enough for one service per module.

## Request flow

```text
Route
  → Controller
    → Form Request: authorize, validate, normalize HTTP input
      → Module Service: query data or perform workflow
        → Eloquent models, storage, PDF/export integrations
  → Inertia response, redirect, download, or JSON response
```

The controller is the HTTP entry point. The service is the module's home for application queries and actions.

## Target directory structure

```text
app/
  Http/
    Controllers/
      BerkasSidangNolController.php
      TemplateSuratController.php
      CarouselController.php
      LinkLandingPageController.php
    Requests/
      SidangNolRequest.php
      TemplateSuratRequest.php
  Services/
    SidangNolService.php
    TemplateSuratService.php
    CarouselService.php
    LinkLandingPageService.php
  Models/
  Policies/
```

Use one root-level Form Request per module. Its `rules()` method may select the
appropriate validation contract from the named route, keeping the module's
request rules discoverable in one file. Split it only when a real, sustained
complexity boundary makes that necessary. Keep one service file per module
unless there is clear evidence that it has become unmanageably large.

## Layer responsibilities

| Layer | Responsible for | Must not own |
| --- | --- | --- |
| Route | URI, HTTP verb, middleware, route model binding | Business logic or database queries |
| Controller | Receive request, call the relevant service, choose response type | Validation rules, query construction, workflow logic |
| Form Request | Input authorization, validation, normalized/validated input | Database workflows, storage actions, response rendering |
| Module Service | Module queries, state changes, transactions, storage/PDF/export orchestration | HTTP redirects, Inertia rendering, reading raw request input |
| Model | Relations, casts, fillable fields, small reusable query scopes | Full workflows or HTTP behavior |
| Policy / middleware | Authorization | Data transformations or workflow side effects |

## Form Requests

A Form Request is Laravel's request object with validation and authorization attached. Laravel runs it before the controller method.

Use one when an endpoint accepts filters, form data, files, or decision input. A plain `Request` remains fine for a genuinely input-free endpoint.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SidangNolRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware enforces the allowed roles.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ];
    }

    public function filters(): array
    {
        $data = $this->validated();

        return [
            'search' => $data['search'] ?? null,
            'per_page' => $data['per_page'] ?? 10,
        ];
    }
}
```

Use `$request->validated()` for form data. Never pass `$request->all()` directly to a model or service. A request helper such as `filters()` is appropriate when it returns a small, named input structure for a service.

## Controller pattern

Controllers should make the endpoint readable without containing its implementation.

```php
use App\Http\Requests\SidangNolRequest;
use App\Services\SidangNolService;
use Inertia\Inertia;

class BerkasSidangNolController extends Controller
{
    public function index(
        SidangNolRequest $request,
        SidangNolService $sidangNolService,
    ) {
        return Inertia::render('BerkasSidangNol/Index', [
            'berkas' => $sidangNolService->datatable(
                user: $request->user(),
                filters: $request->filters(),
            ),
        ]);
    }
}
```

Laravel resolves the service automatically through dependency injection. The controller may call `$this->authorize(...)` or rely on explicit route middleware, depending on the authorization requirement.

### Controller action names

Use Laravel resource names for ordinary record operations: `index`, `create`,
`store`, `show`, `edit`, `update`, and `destroy`. Use these workflow and file
action names consistently in every letter module:

| Intent | Controller action |
| --- | --- |
| Submit a draft | `submit` |
| Render the staff review page | `review` |
| Record an approval or rejection | `decide` |
| Generate and archive the final letter | `finalize` |
| Return an attachment for inline preview | `showAttachment` |
| Return a legacy document for inline preview | `showDocument` |
| Download the generated final letter | `downloadFinalLetter` |
| Reset a legacy record to draft | `resetToDraft` |
| Export records | `export` |

Keep route URIs stable for live modules unless a URL migration is explicitly
approved. Named routes should use the same verb in kebab case, for example
`cuti-akademik.review`, `cuti-akademik.decide`, and
`cuti-akademik.download-final-letter`.

## Module service pattern

Every retained module has one service file. Its methods use business-oriented names and receive explicit values rather than an entire HTTP request.

```php
namespace App\Services;

use App\Models\BerkasSidangNol;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SidangNolService
{
    public function datatable(User $user, array $filters): LengthAwarePaginator
    {
        return BerkasSidangNol::query()
            ->with('user')
            ->when(
                $user->role_id === 1,
                fn ($query) => $query->where('user_id', $user->id),
                fn ($query) => $query->where('status', '>=', 1),
            )
            ->when($filters['search'], function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('nama', 'like', "%{$search}%")
                                ->orWhere('nim', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'])
            ->withQueryString();
    }
}
```

### Service method contract

Public service methods are an application contract. Use the following names and
parameter order for ordinary module records. This is the required convention for
new work and when a touched module is refactored.

| Need | Required service method |
| --- | --- |
| Paginated index data | `datatable(User $actor, array $filters)` |
| One record for view/edit | `findForDisplay(Model $record, User $actor)` or a similarly explicit, authorized finder |
| Create a record or draft | `create(User $actor, array $data)` |
| Update a record or draft | `update(Model $record, User $actor, array $data)` |
| Delete a record and owned files | `delete(Model $record, User $actor)` |
| Domain workflow | A specific verb such as `submit()`, `decide()`, `finalize()`, `resetPassword()`, or `download()` |

Omit `User $actor` only when neither visibility nor authorization depends on the
authenticated user, such as a public-template query already constrained by its
own `publishedOnly` argument. Keep the remaining order: the existing model
first, then actor, then data. Use the concrete model type in code rather than
the `Model` placeholder shown above.

These names are strict:

- Use `datatable()` for the service method that returns paginated index data.
  `paginate()` remains an Eloquent builder call inside that method; it is not a
  public module-service method.
- Use distinct `create()` and `update()` methods. A public `save()` method that
  decides between creation and update is prohibited. Calling Eloquent's
  `$model->save()` internally remains valid.
- Use `delete()` for deletion and a descriptive domain verb for an action that
  is not ordinary CRUD. Do not hide a workflow behind `process()`, `handle()`,
  `data()`, `action()`, or another generic name.
- Use descriptive parameter names (`$filters`, `$data`, `$user`,
  `$templateSurat`) rather than abbreviations such as `$f`, `$d`, or `$u`.
- Give every public service method an explicit return type. Add PHPDoc array
  shapes when an input or return array has a defined contract.

For example, a service that does not need an actor for its own authorization
still uses the same CRUD names:

```php
public function datatable(array $filters, bool $publishedOnly = false): LengthAwarePaginator;

/** @param array<string, mixed> $data */
public function create(array $data): TemplateSurat;

/** @param array<string, mixed> $data */
public function update(TemplateSurat $templateSurat, array $data): TemplateSurat;
```

## Letter PDF configuration

`LetterService` owns the finalisation transaction, generated-file storage,
archive state, and failure cleanup. Every concrete Letter Type service supplies
its own immutable document configuration:

```php
protected function pdfView(): string;

/** @return array<string, mixed> */
protected function pdfData(Letter $letter, array $signatory): array;
```

`PdfGeneratorService` only converts that Blade view and data into PDF bytes. It
does not contain Letter Type conditions. PDF data must come from the saved
`Letter`, its `meta` snapshot, and the selected signatory snapshot—not live
profile data or raw request values. A module can override `pdfPaper()`,
`pdfOrientation()`, or `finalPdfPath()` only when its official format requires
it.

## Letter module standard

All future academic-letter modules use the same backend shape as Cuti Akademik.
The concrete service is the only letter-specific backend class; do not add a
separate `XxxPdfGenerator`, repository, action, or workflow service.

```text
app/
  Http/
    Controllers/
      CutiAkademikController.php
    Requests/
      CutiAkademikRequest.php
  Services/
    LetterService.php             # shared abstract workflow
    PdfGeneratorService.php       # shared DomPDF renderer
    LetterAttachmentService.php   # shared secure-file persistence
    CutiAkademikService.php       # one concrete Letter Type service
resources/
  views/letters/
    cuti-akademik.blade.php       # one official template per Letter Type
database/
  seeders/
    CutiAkademikLetterTypeSeeder.php
```

### Controller and Request names

Use one controller and one root-level Form Request named after the Letter Type:
`MagangController` and `MagangRequest`, for example. The controller remains
thin and uses the existing action names:

| Intent | Controller method | Service method |
| --- | --- | --- |
| Table data | `index` | `datatable()` |
| New draft screen | `create` | — |
| Create draft | `store` | `create()` |
| Display one letter | `show` | `findForDisplay()` |
| Edit draft screen | `edit` | `findForDisplay()` + policy |
| Save draft changes | `update` | `update()` |
| Delete draft | `destroy` | `delete()` |
| Submit applicant request | `submit` | `ajuan()` |
| Staff review page | `review` | `findForDisplay()` |
| Forward or reject | `decide` | `decide()` |
| Render/archive final PDF | `finalize` | `finalize()` |
| Preview attachment | `showAttachment` | `findViewableAttachment()` |
| Download final PDF | `downloadFinalLetter` | `findForDisplay()` |

Keep the public controller and route action named `submit` for a stable HTTP
contract. `ajuan()` is the intentional business name inside `LetterService`.
The Form Request selects route-specific rules and exposes named helpers such as
`filters()` and `payload()`; it never owns database or storage work.

### Concrete Letter Type service contract

Every service extending `LetterService` must implement these protected methods:

```php
protected function letterTypeCode(): string;
protected function finalizationRole(): string;
protected function signatoryRole(): string;
protected function pdfView(): string;

/** @return array<string, mixed> */
protected function pdfData(Letter $letter, array $signatory): array;
```

- `letterTypeCode()` returns the unique seeded code, for example
  `CUTI_AKADEMIK`.
- `finalizationRole()` is the **last** approval-chain role. This actor enters
  the manual official number, generates the PDF, and archives the request.
- `signatoryRole()` is the default official document signer role. It may be an
  earlier approval role, such as DEKAN when RESEPSIONIS finalizes Cuti
  Akademik. A module may expose a finalization-time role selector, but the
  service must validate that the selected role belongs to that Letter's
  approval chain and that the selected user has that role. For a service such
  as Bebas Ruang Baca, both roles may be `RUANG BACA`.
- `pdfView()` returns the Blade view name, such as `letters.cuti-akademik`.
- `pdfData()` returns only the variables required by that Blade template. Use
  saved `Letter` fields, `Letter::meta`, and the signatory snapshot.

The concrete service normally owns `datatable()`, `create()`, `update()`,
type-specific metadata construction, applicant checks, and review-scope rules.
It inherits `ajuan()`, `decide()`, `finalize()`, `delete()`,
`findForDisplay()`, `findViewableAttachment()`,
`availableFinalSignatories()`, and `isFinalizationStage()`.

Do not override inherited workflow methods merely to rename them. Override a
protected hook only when the Letter Type has a proven different requirement:
`ensureReviewerScope()`, `applyReviewerVisibility()`, `pdfPaper()`,
`pdfOrientation()`, or `finalPdfPath()`.

### Letter Type seed and data rules

Create an idempotent `XxxLetterTypeSeeder` and its production-safe seed
migration only when a Letter Type is approved. The seed defines the unique
code, display name, category, applicant roles, notes Markdown, and approval
chain. Its `pdf_template` value documents the official template and should
match the view returned by `pdfView()`.

Store applicant identity as a server-created snapshot in `letters`; store only
Letter Type-specific fields in `letters.meta`; store supporting files in
`letter_attachments`. Do not read current user biodata while rendering a final
PDF. The generated final file is immutable after archival; changing a Blade
template affects only future finalizations unless an explicit, audited PDF
regeneration feature is approved.

### SUPER ADMIN workflow override

When a Letter module permits SUPER ADMIN, the service—not only the route
middleware—must explicitly allow it to view all records, manage any draft, and
record a decision at any in-progress stage. This is an administrative override,
not a shortcut around Letter state: the request must still be a draft for draft
actions, in progress for a decision, and at the configured finalization stage
for `finalize()`.

SUPER ADMIN does not bypass signer constraints. `availableFinalSignatories()`
and `finalize()` must still return and validate only users with the selected
approval-chain signer role. If SUPER ADMIN edits another applicant's draft,
preserve the applicant snapshot and reviewer-scope metadata rather than
replacing it with the administrator's profile.

## Queries, scopes, and transactions

- A module service owns the complete query assembled for an endpoint.
- A model may expose a small, reusable scope such as `->submitted()` or `->forPeriod($start, $end)`. Services compose those scopes; controllers do not.
- A service must wrap multi-write workflows in `DB::transaction()`. Sidang Nol decisions are an example: update status, create a note, generate/store a response letter, and persist its path as one coherent operation.
- Keep private file handling in the service that owns the workflow and use the configured `secure` disk for student documents.
- Return models, paginator objects, arrays, or export/download objects from services—not redirects or `Inertia::render()` responses.

## Authorization rules

1. Route middleware provides broad route access, such as `auth` and allowed roles.
2. Policies or controller authorization protect a specific record where ownership matters.
3. Services receive the acting `User` for state-changing actions and enforce important workflow invariants as a second line of protection.
4. Frontend menu visibility never replaces server-side authorization.

## What not to add yet

- No Repository layer merely to wrap Eloquent calls.
- No separate service file for every individual action.
- No generic `BaseService` with shared CRUD methods. `LetterService` is the
  explicit exception for the shared Letters foundation only; it owns the
  generic Letter finalization workflow and asks each Letter Type service for
  its PDF template and saved-data payload.
- No service method that accepts `Request $request`; pass validated arrays, models, and the acting user instead.

If one module service becomes difficult to navigate after real growth, split
only a proven independent domain concern. Keep PDF rendering itself in the
shared `PdfGeneratorService`; do not create a generator class for every Letter
Type.

## Shared Letter attachments

The Letters foundation is the narrow exception to the one-service-per-module
rule. `LetterAttachmentService` is shared because every future Letter service
uses `letter_attachments` and the same opaque secure-disk contract. It may
store, check, and remove a Letter's private files; it must not decide module
workflow, authorization policy, labels, or page behavior. The owning service,
such as `CutiAkademikService`, remains responsible for count requirements,
state transitions, and record authorization.

`LetterService` is an abstract parent for module services such as
`CutiAkademikService`. It owns only proven shared behavior: Letter Type
identity, draft ownership, view/file authorization, approval-chain transitions,
transaction locks, audit logs, final-signatory lookups, and final PDF/archive
work. Each child supplies its roles and PDF configuration, while retaining its
own `create`, `update`, metadata, and review-scope behavior. Use constructor
injection for shared collaborators; do not call `app()` from a module service.

## Refactor checklist

For each retained module, make one endpoint work end-to-end before moving to the next.

1. Add or update the Form Request and its validation tests.
2. Move its controller query or workflow into the module service without changing the frontend response contract.
3. Keep middleware and authorization behavior unchanged unless the task explicitly improves it.
4. Test the service behavior, then the HTTP endpoint's validation and authorization.
5. Remove the old controller logic only after the replacement passes verification.
6. Update the relevant module document in `docs/modules/` when behavior or data contracts change.
