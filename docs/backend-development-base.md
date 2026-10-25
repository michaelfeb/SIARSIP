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

Suggested method names for `SidangNolService`:

| Need | Method name |
| --- | --- |
| Paginated index data | `datatable(User $user, array $filters)` |
| One record for view/edit | `findForDisplay(int $id)` |
| Create or update draft | `save(array $data, User $actor, ?BerkasSidangNol $berkas = null)` |
| Send a draft | `submit(BerkasSidangNol $berkas, User $actor)` |
| Accept or reject | `decide(BerkasSidangNol $berkas, array $data, User $actor)` |
| Reset rejected record | `reset(BerkasSidangNol $berkas, User $actor)` |
| Delete record and private files | `delete(BerkasSidangNol $berkas, User $actor)` |
| Produce export | `export(array $filters)` |

`datatable()` is acceptable because it clearly represents the paginated table used by this application. Prefer similarly explicit names; avoid vague methods such as `process()`, `handle()`, or `data()`.

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
identity, draft ownership, view/file authorization, approval-chain submit and
decision transitions, transaction locks, audit logs, and final-signatory
lookups. Each child service implements `letterTypeCode()`,
`finalizationRole()`, and `signatoryRole()`. The finalization role must be the
last entry of the Letter Type's approval chain; the signatory role may be a
different earlier role, such as DEKAN when RESEPSIONIS finalizes Cuti
Akademik. Each child retains its own `create`, `update`, and `finalize`
behavior because form metadata, review scope, and PDF output vary by letter
type. Use constructor injection for shared collaborators such as
`LetterAttachmentService`; do not call `app()` from a module service.

## Refactor checklist

For each retained module, make one endpoint work end-to-end before moving to the next.

1. Add or update the Form Request and its validation tests.
2. Move its controller query or workflow into the module service without changing the frontend response contract.
3. Keep middleware and authorization behavior unchanged unless the task explicitly improves it.
4. Test the service behavior, then the HTTP endpoint's validation and authorization.
5. Remove the old controller logic only after the replacement passes verification.
6. Update the relevant module document in `docs/modules/` when behavior or data contracts change.
